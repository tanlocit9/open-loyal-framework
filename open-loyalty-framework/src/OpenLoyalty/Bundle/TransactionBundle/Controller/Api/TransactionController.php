<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Controller\Api;

use Broadway\ReadModel\Repository;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\ImportBundle\Form\Type\ImportFileFormType;
use OpenLoyalty\Bundle\ImportBundle\Service\ImportFileManager;
use OpenLoyalty\Bundle\TransactionBundle\Form\Handler\AppendLabelsToTransactionFormHandler;
use OpenLoyalty\Bundle\TransactionBundle\Form\Handler\EditTransactionLabelsFormHandler;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\AppendLabelsToTransactionFormType;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\EditTransactionLabelsFormType;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\LabelsFilterFormType;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\ManuallyAssignCustomerToTransactionFormType;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\TransactionFormType;
use OpenLoyalty\Bundle\TransactionBundle\Form\Type\TransactionSimulationFormType;
use OpenLoyalty\Bundle\TransactionBundle\Import\TransactionXmlImporter;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\EarningRule\Domain\OloyEarningRuleEvaluator;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;
use OpenLoyalty\Component\Transaction\Domain\Exception\InvalidTransactionReturnDocumentNumberException;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionController.
 *
 * @Security("is_granted('ROLE_USER')")
 */
class TransactionController extends FOSRestController
{
    /**
     * Method will return complete list of all transactions.
     *
     * @Route(name="oloy.transaction.list", path="/transaction")
     * @Route(name="oloy.transaction.customer.list", path="/customer/transaction")
     * @Route(name="oloy.transaction.seller.list", path="/seller/transaction")
     * @Security("is_granted('LIST_TRANSACTIONS') or is_granted('LIST_CURRENT_CUSTOMER_TRANSACTIONS') or is_granted('LIST_CURRENT_POS_TRANSACTIONS')")
     * @Method("GET")
     *
     * @ApiDoc(
     *     name="get transactions list",
     *     section="Transactions",
     *     parameters={
     *      {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *      {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *      {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *      {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @param Request      $request
     * @param ParamFetcher $paramFetcher
     *
     * @return View
     *
     * @QueryParam(name="customerData_loyaltyCardNumber", nullable=true, description="loyaltyCardNumber"))
     * @QueryParam(name="documentType", nullable=true, description="documentType"))
     * @QueryParam(name="customerData_name", nullable=true, description="customerName"))
     * @QueryParam(name="customerData_email", nullable=true, description="customerEmail"))
     * @QueryParam(name="customerData_phone", nullable=true, description="customerPhone"))
     * @QueryParam(name="customerId", nullable=true, description="customerId"))
     * @QueryParam(name="documentNumber", nullable=true, description="transactionId"))
     * @QueryParam(name="posId", nullable=true, description="posId"))
     */
    public function listAction(Request $request, ParamFetcher $paramFetcher): View
    {
        $filterForm = $this->get('form.factory')->createNamed('', LabelsFilterFormType::class, null, ['method' => 'GET']);
        $filterForm->handleRequest($request);
        $params = $this->get('oloy.user.param_manager')->stripNulls($paramFetcher->all(), true, false);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $params['labels'] = $filterForm->getData()['labels'];
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_PARTICIPANT')) {
            $params['customerId'] = $user->getId();
        }
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request, 'purchaseDate', 'DESC');

        /** @var TransactionDetailsRepository $repo */
        $repo = $this->get(TransactionDetailsRepository::class);

        $transactions = $repo->findByParametersPaginated(
            $params,
            false,
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );
        $total = $repo->countTotal($params, false);

        return $this->view([
            'transactions' => $transactions,
            'total' => $total,
        ], 200);
    }

    /**
     * Method will return logged in customer transactions.
     *
     * @Route(name="oloy.transaction.seller.list_customer_transactions", path="/seller/transaction/customer/{customer}")
     * @Security("is_granted('LIST_CUSTOMER_TRANSACTIONS', customer)")
     * @Method("GET")
     *
     * @ApiDoc(
     *     name="get transactions list",
     *     section="Transactions",
     *     parameters={
     *      {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *      {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *      {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *      {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @param Request         $request
     * @param ParamFetcher    $paramFetcher
     * @param CustomerDetails $customer
     *
     * @return View
     *
     * @QueryParam(name="documentNumber", nullable=true, description="documentNumber"))
     */
    public function listCustomerAction(Request $request, ParamFetcher $paramFetcher, CustomerDetails $customer): View
    {
        $params = $this->get('oloy.user.param_manager')->stripNulls($paramFetcher->all(), true, false);
        $params['customerId'] = $customer->getCustomerId()->__toString();

        $pagination = $this->get('oloy.pagination')->handleFromRequest($request, 'purchaseDate', 'DESC');

        /** @var TransactionDetailsRepository $repo */
        $repo = $this->get(TransactionDetailsRepository::class);

        $transactions = $repo->findByParametersPaginated(
            $params,
            false,
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );
        $total = $repo->countTotal($params, false);

        return $this->view([
            'transactions' => $transactions,
            'total' => $total,
        ], 200);
    }

    /**
     * Method will return transactions with provided document number.
     *
     * @Route(name="oloy.transaction.seller.list_by_document_number", path="/seller/transaction/{documentNumber}")
     * @Method("GET")
     * @Security("is_granted('LIST_CURRENT_POS_TRANSACTIONS')")
     *
     * @ApiDoc(
     *     name="get transactions list by documentNumber",
     *     section="Transactions",
     * )
     *
     * @param $documentNumber
     *
     * @return View
     */
    public function listByDocumentNumberAction($documentNumber): View
    {
        /** @var TransactionDetailsRepository $repo */
        $repo = $this->get(TransactionDetailsRepository::class);

        $transactions = $repo->findByParameters(
            ['documentNumber' => $documentNumber]
        );

        $visible = [];
        foreach ($transactions as $transaction) {
            if ($this->isGranted('VIEW', $transaction)) {
                $visible[] = $transaction;
            }
        }

        return $this->view([
            'transactions' => $visible,
            'total' => count($visible),
        ], 200);
    }

    /**
     * Method wil return available labels.
     *
     * @Route(name="oloy.transaction.get_item_labels", path="/transaction/item/labels")
     * @Method("GET")
     * @Security("is_granted('LIST_ITEM_LABELS')")
     * @ApiDoc(
     *     name="get transactions items labels list",
     *     section="Transactions",
     * )
     *
     * @return View
     */
    public function getItemLabelsAction(): View
    {
        /** @var TransactionDetailsRepository $repo */
        $repo = $this->get(TransactionDetailsRepository::class);
        $labels = $repo->getAvailableLabels();

        return $this->view([
            'labels' => $labels,
        ], 200);
    }

    /**
     * Method will return transaction details.
     *
     * @Route(name="oloy.transaction.get", path="/transaction/{transaction}")
     * @Route(name="oloy.transaction.customer.get", path="/customer/transaction/{transaction}")
     * @Method("GET")
     * @Security("is_granted('VIEW', transaction)")
     * @ApiDoc(
     *     name="get transaction",
     *     section="Transactions",
     * )
     *
     * @param TransactionDetails $transaction
     *
     * @return View
     */
    public function getAction(TransactionDetails $transaction): View
    {
        return $this->view($transaction, 200);
    }

    /**
     * Method allows to register new transaction in system.
     *
     * @Route(name="oloy.transaction.register", path="/transaction")
     * @Method("POST")
     * @Security("is_granted('CREATE_TRANSACTION')")
     * @ApiDoc(
     *     name="Register transaction",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\TransactionBundle\Form\Type\TransactionFormType", "name" = "transaction"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function registerAction(Request $request): View
    {
        $form = $this->get('form.factory')->createNamed('transaction', TransactionFormType::class);
        $form->handleRequest($request);
        $returnsEnabled = $this->get('ol.settings.manager')->getSettingByKey('returns');
        $returnsEnabled = $returnsEnabled ? $returnsEnabled->getValue() : false;

        if ($form->isValid()) {
            $data = $form->getData();
            if ($data['transactionData']['documentType'] == Transaction::TYPE_RETURN && !$returnsEnabled) {
                $form->get('transactionData')->get('documentType')->addError(new FormError('Returns are not enabled'));

                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }
            $transactionId = new TransactionId($this->get('broadway.uuid.generator')->generate());
            $settingsManager = $this->get('ol.settings.manager');
            $excludedSKUs = $settingsManager->getSettingByKey('excludedDeliverySKUs');
            $excludedLevelSKUs = $settingsManager->getSettingByKey('excludedLevelSKUs');
            $excludedCategories = $settingsManager->getSettingByKey('excludedLevelCategories');

            try {
                $this->get('broadway.command_handling.command_bus')->dispatch(
                    new RegisterTransaction(
                        $transactionId,
                        $data['transactionData'],
                        $data['customerData'],
                        $data['items'],
                        isset($data['pos']) ? new PosId($data['pos']) : null,
                        $excludedSKUs ? $excludedSKUs->getValue() : null,
                        $excludedLevelSKUs ? $excludedLevelSKUs->getValue() : null,
                        $excludedCategories ? $excludedCategories->getValue() : null,
                        $data['revisedDocument'],
                        $data['labels']
                    )
                );
            } catch (InvalidTransactionReturnDocumentNumberException $exception) {
                $form->get('revisedDocument')->addError(new FormError($exception->getMessage()));

                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->view(['transactionId' => (string) $transactionId]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to edit transaction labels.
     *
     * @Route(name="oloy.transaction.edit_labels", path="/admin/transaction/labels")
     * @Method("POST")
     * @Security("is_granted('EDIT_TRANSACTION_LABELS')")
     * @ApiDoc(
     *     name="Edit transaction labels",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\TransactionBundle\Form\Type\EditTransactionLabelsFormType", "name" = "transaction_labels"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request                          $request
     * @param EditTransactionLabelsFormHandler $handler
     *
     * @return View
     */
    public function editLabelsAction(Request $request, EditTransactionLabelsFormHandler $handler): View
    {
        $form = $this->get('form.factory')->createNamed('transaction_labels', EditTransactionLabelsFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $result = $handler->onSuccess($form);

            if ($result === false) {
                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->view(['transactionId' => $result->__toString()]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return number of points which can be obtained after registering such transaction.<br/>
     * It will not change anything in the system.
     *
     * @Route(name="oloy.transaction.simulate", path="/transaction/simulate")
     * @Method("POST")
     * @ApiDoc(
     *     name="Simulate transaction",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\TransactionBundle\Form\Type\TransactionSimulationFormType", "name" = "transaction"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function simulateAction(Request $request): View
    {
        $form = $this->get('form.factory')->createNamed('transaction', TransactionSimulationFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $items = $data['items'];
            $itemsObjects = [];
            foreach ($items as $item) {
                if ($item instanceof Item) {
                    $itemsObjects[] = $item;
                } else {
                    $itemsObjects[] = Item::deserialize($item);
                }
            }
            $settingsManager = $this->get('ol.settings.manager');
            $excludedSKUs = $settingsManager->getSettingByKey('excludedDeliverySKUs');

            $transaction = Transaction::createTransaction(
                new TransactionId($this->get('broadway.uuid.generator')->generate()),
                [],
                [],
                $itemsObjects,
                null,
                $excludedSKUs ? $excludedSKUs->getValue() : null
            );

            $points = $this->get(OloyEarningRuleEvaluator::class)->evaluateTransaction($transaction, null);

            return $this->view(['points' => $points]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to assign customer to specific transaction.
     *
     * @Route(name="oloy.transaction.assign_customer", path="/admin/transaction/customer/assign")
     * @Route(name="oloy.transaction.customer.assign_customer", path="/customer/transaction/customer/assign")
     * @Route(name="oloy.transaction.pos.assign_customer", path="/pos/transaction/customer/assign")
     * @Method("POST")
     * @ApiDoc(
     *     name="Assign customer to transaction",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\TransactionBundle\Form\Type\ManuallyAssignCustomerToTransactionFormType", "name" = "assign"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function assignCustomerAction(Request $request): View
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isGranted('ROLE_PARTICIPANT')) {
            $parameters = $request->request->get('assign');
            $parameters['customerId'] = $user->getId();
            unset($parameters['customerLoyaltyCardNumber'], $parameters['customerPhoneNumber']);
            $request->request->set('assign', $parameters);
        }

        /** @var ManuallyAssignCustomerToTransactionFormType|FormInterface $form */
        $form = $this->get('form.factory')->createNamed('assign', ManuallyAssignCustomerToTransactionFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $result = $this->get('oloy.transaction.form_handler.manually_assign_customer_to_transaction')->onSuccess($form);

            if ($result === false) {
                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->view(['transactionId' => (string) $result]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows customer to append new labels to his transaction.
     *
     * @Route(name="oloy.transaction.customer.append_labels", path="/customer/transaction/labels/append")
     * @Method("PUT")
     * @ApiDoc(
     *     name="Append labels to customer transaction",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\TransactionBundle\Form\Type\AppendLabelsToTransactionFormType", "name" = "append"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @Security("is_granted('ROLE_PARTICIPANT')")
     *
     * @param Request                              $request
     * @param AppendLabelsToTransactionFormHandler $handler
     *
     * @return View
     */
    public function appendLabelsAction(Request $request, AppendLabelsToTransactionFormHandler $handler): View
    {
        /** @var ManuallyAssignCustomerToTransactionFormType|FormInterface $form */
        $form = $this->get('form.factory')->createNamed('append', AppendLabelsToTransactionFormType::class, null, [
            'method' => 'PUT',
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $result = $handler->onSuccess($form);

            if ($result === false) {
                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->view(['transactionId' => $result->__toString()]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Import transactions.
     *
     * @Route(name="oloy.transaction.import", path="/admin/transaction/import")
     * @Method("POST")
     * @Security("is_granted('CREATE_TRANSACTION')")
     * @ApiDoc(
     *     name="Import transactions",
     *     section="Transactions",
     *     input={"class" = "OpenLoyalty\Bundle\ImportBundle\Form\Type\ImportFileFormType", "name" = "file"}
     * )
     *
     * @param Request                $request
     * @param TransactionXmlImporter $importer
     * @param ImportFileManager      $importFileManager
     *
     * @return View
     *
     * @throws \Exception
     */
    public function importAction(
        Request $request,
        TransactionXmlImporter $importer,
        ImportFileManager $importFileManager
    ): View {
        $form = $this->get('form.factory')->createNamed('file', ImportFileFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()->getFile();
            $importFile = $importFileManager->upload($file, 'transactions');
            $result = $importer->import($importFileManager->getAbsolutePath($importFile));

            return $this->view($result, Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param SellerId $id
     *
     * @return SellerDetails|null
     */
    protected function getSellerDetails(SellerId $id)
    {
        /** @var Repository $repo */
        $repo = $this->get('oloy.user.read_model.repository.seller_details');

        return $repo->find((string) $id);
    }
}
