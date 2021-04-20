<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Unit\ParamConverter;

use OpenLoyalty\Bundle\CampaignBundle\ParamConverter\CampaignParamConverter;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CampaignParamConverterTest.
 */
final class CampaignParamConverterTest extends TestCase
{
    /**
     * @var CampaignRepository|MockObject
     */
    private $campaignRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ParamConverter|MockObject
     */
    private $parameterConverter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->campaignRepository = $this->createMock(CampaignRepository::class);
        $this->request = new Request();
        $this->parameterConverter = $this->createMock(ParamConverter::class);
    }

    /**
     * @test
     */
    public function it_return_404_not_found_exception_when_campaign_id_is_incorrect(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $converter = new CampaignParamConverter($this->campaignRepository);

        $this->parameterConverter->expects($this->once())->method('getName')->willReturn('campaign');
        $this->request->attributes->add(['campaign' => '{campaign}']);

        $converter->apply($this->request, $this->parameterConverter);
    }
}
