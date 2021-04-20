<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ActivationCodeBundle\Service;

use Assert\AssertionFailedException;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use OpenLoyalty\Bundle\ActivationCodeBundle\Exception\SmsSendException;
use OpenLoyalty\Bundle\ActivationCodeBundle\Generator\CodeGenerator;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\SmsApiBundle\Message\Message;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCode;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCodeId;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCodeRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ActivationCodeManager.
 */
class ActivationCodeManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var SmsSender
     */
    protected $smsSender;

    /**
     * @var int
     */
    protected $codeLength = 6;

    /**
     * @var CodeGenerator
     */
    protected $codeGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var GeneralSettingsManagerInterface
     */
    private $generalSettingsManager;

    /**
     * ActivationCodeManager constructor.
     *
     * @param UuidGeneratorInterface          $uuidGenerator
     * @param EntityManager                   $em
     * @param TranslatorInterface             $translator
     * @param CodeGenerator                   $codeGenerator
     * @param GeneralSettingsManagerInterface $generalSettingsManager
     * @param int                             $codeLength
     */
    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        EntityManager $em,
        TranslatorInterface $translator,
        CodeGenerator $codeGenerator,
        GeneralSettingsManagerInterface $generalSettingsManager,
        int $codeLength
    ) {
        $this->em = $em;
        $this->uuidGenerator = $uuidGenerator;
        $this->codeGenerator = $codeGenerator;
        $this->translator = $translator;
        $this->generalSettingsManager = $generalSettingsManager;
        $this->codeLength = $codeLength;
    }

    /**
     * @param SmsSender $smsSender
     */
    public function setSmsSender(SmsSender $smsSender)
    {
        $this->smsSender = $smsSender;
    }

    /**
     * @param CodeGenerator $codeGenerator
     */
    public function setCodeGenerator(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * @return int
     */
    public function getCodeLength(): int
    {
        return $this->codeLength;
    }

    /**
     * @param int $codeLength
     */
    public function setCodeLength(int $codeLength)
    {
        $this->codeLength = $codeLength;
    }

    /**
     * @param string $code
     * @param string $objectType
     *
     * @return null|object|ActivationCode
     */
    public function findCode(string $code, string $objectType)
    {
        return $this->em->getRepository(ActivationCode::class)->findOneBy([
            'code' => $code,
            'objectType' => $objectType,
        ]);
    }

    /**
     * @param string $code
     * @param string $objectType
     *
     * @return null|object|ActivationCode|string
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function findValidCode(string $code, string $objectType)
    {
        $entity = $this->findCode($code, $objectType);
        if (null !== $entity) {
            $lastCode = $this->em->getRepository(ActivationCode::class)->getLastByObjectTypeAndObjectId(
                $objectType,
                $entity->getObjectId()
            );

            if (0 === strcasecmp($lastCode->getObjectId(), $entity->getObjectId())) {
                return $lastCode;
            }
        }

        return;
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return \DateTime|void
     */
    public function getLastCodeGenerationDate($objectType, $objectId)
    {
        /** @var ActivationCodeRepositoryInterface $repository */
        $repository = $this->em->getRepository(ActivationCode::class);
        $lastCode = $repository->getLastByObjectTypeAndObjectId($objectType, $objectId);

        if (!$lastCode instanceof ActivationCode) {
            return;
        }

        return $lastCode->getCreatedAt();
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return ActivationCode
     */
    public function newCode(string $objectType, string $objectId)
    {
        try {
            $entity = new ActivationCode(
                new ActivationCodeId($this->uuidGenerator->generate()),
                $objectType,
                $objectId,
                $this->generateUniqueCode($objectType, $objectId)
            );

            $this->persistUniqueActivationCode($entity);
        } catch (AssertionFailedException $e) {
            return;
        } catch (OptimisticLockException $e) {
            return;
        } catch (UniqueConstraintViolationException $e) {
            return;
        }

        return $entity;
    }

    /**
     * @param ActivationCode $code
     * @param string         $phone
     *
     * @return bool
     *
     * @throws SmsSendException
     */
    public function sendCode(ActivationCode $code, $phone)
    {
        try {
            /** @var ActivationCodeRepositoryInterface $repository */
            $repository = $this->em->getRepository(ActivationCode::class);
            $codeNo = $repository->countByObjectTypeAndObjectId($code->getObjectType(), $code->getObjectId());
        } catch (NonUniqueResultException $e) {
            return false;
        } catch (NoResultException $e) {
            return false;
        }

        $content = sprintf(
            '%s activation code (no. %d): %s',
            $this->generalSettingsManager->getProgramName(),
            $codeNo,
            $code->getCode()
        );

        $msg = Message::create($phone, $this->generalSettingsManager->getProgramName(), $content);

        return $this->getSmsSender()->send($msg);
    }

    /**
     * @param ActivationCode $code
     * @param string         $phone
     *
     * @return bool
     *
     * @throws SmsSendException
     */
    public function sendResetCode(ActivationCode $code, $phone)
    {
        try {
            /** @var ActivationCodeRepositoryInterface $repository */
            $repository = $this->em->getRepository(ActivationCode::class);
            $codeNo = $repository->countByObjectTypeAndObjectId(
                $code->getObjectType(),
                $code->getObjectId()
            );
        } catch (NonUniqueResultException $e) {
            return false;
        } catch (NoResultException $e) {
            return false;
        }

        $content = $this->translator->trans('activation.reset_code.content', [
            '%program_name%' => $this->generalSettingsManager->getProgramName(),
            '%code%' => $code->getCode(),
            '%code_number%' => $codeNo,
        ]);

        $msg = Message::create($phone, $this->generalSettingsManager->getProgramName(), $content);

        return $this->getSmsSender()->send($msg);
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     *
     * @throws SmsSendException
     */
    public function resendCode(Customer $customer)
    {
        $lastCodeGenerationDate = $this->getLastCodeGenerationDate(Customer::class, $customer->getId());
        $deadline = new \DateTime('-2 minutes');
        if ($lastCodeGenerationDate && $deadline < $lastCodeGenerationDate) {
            return false;
        }

        $code = $this->newCode(Customer::class, $customer->getId());
        if (!$code) {
            return false;
        }

        return $this->sendCode($code, $customer->getPhone());
    }

    /**
     * @param string $temporaryPassword
     * @param string $phone
     *
     * @return bool
     *
     * @throws SmsSendException
     */
    public function sendTemporaryPassword($temporaryPassword, $phone)
    {
        $content = $this->translator->trans('activation.temporary_password.content', [
            '%program_name%' => $this->generalSettingsManager->getProgramName(),
            '%password%' => $temporaryPassword,
        ]);

        $msg = Message::create($phone, $this->generalSettingsManager->getProgramName(), $content);

        return $this->getSmsSender()->send($msg);
    }

    /**
     * @return bool
     */
    public function hasNeededSettings()
    {
        if (null === $this->smsSender) {
            return false;
        }

        return $this->smsSender->hasNeededSettings();
    }

    /**
     * @param ActivationCode $entity
     * @param int            $tryLimit
     *
     * @throws UniqueConstraintViolationException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function persistUniqueActivationCode(ActivationCode $entity, $tryLimit = 7)
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            if ($tryLimit-- == 0) {
                throw $e;
            }
            $entity->setCode($this->generateUniqueCode($entity->getObjectType(), $entity->getObjectId()));
            $this->persistUniqueActivationCode($entity, $tryLimit);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                if ($tryLimit-- == 0) {
                    throw $e;
                }

                $entity->setCode($this->generateUniqueCode($entity->getObjectType(), $entity->getObjectId()));
                $this->persistUniqueActivationCode($entity, $tryLimit);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return string
     */
    protected function generateUniqueCode(string $objectType, string $objectId)
    {
        while (true) {
            $code = $this->generateCode($objectType, $objectId);
            $entity = $this->em->getRepository(ActivationCode::class)->findOneBy([
                'code' => $code,
            ]);

            if (null === $entity) {
                return $code;
            }
        }
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return string
     */
    protected function generateCode(string $objectType, string $objectId)
    {
        return $this->codeGenerator->generate($objectType, $objectId, $this->getCodeLength());
    }

    /**
     * @return SmsSender
     */
    private function getSmsSender(): SmsSender
    {
        if (!$this->smsSender) {
            throw new \RuntimeException('Sms gateway not set');
        }

        return $this->smsSender;
    }
}
