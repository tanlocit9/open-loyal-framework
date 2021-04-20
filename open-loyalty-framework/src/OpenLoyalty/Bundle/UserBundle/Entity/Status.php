<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenLoyalty\Component\Customer\Domain\Model\Status as Model;

/**
 * Class Status.
 *
 * @ORM\Embeddable()
 */
class Status extends Model
{
    /**
     * @var string
     * @ORM\Column(type = "string")
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type = "string")
     */
    protected $state;
}
