<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain Inc. <info@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DurationBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\HookType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use CampaignChain\CoreBundle\Form\Type\DaterangepickerType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DurationType extends HookType
{
    protected $container;
    protected $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->datetime = $this->container->get('campaignchain.core.util.datetime');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', new DaterangepickerType(), array(
                'label' => 'Start',
//                'data' => $options['data']->getStartDate(),
                'model_timezone' => 'UTC',
                'view_timezone' => $this->datetime->getUserTimezone(),
                'widget' => 'single_text',
                'format' => $this->datetime->getUserDatetimeFormat(),
                'date_format' => $this->datetime->getUserDateFormat(),
                'input' => 'datetime',
                'attr' => array(
                    'placeholder' => 'Select a date range',
                    'input_group' => array(
                        'append' => '<span class="fa fa-calendar">',
                    ),
                )
            ))
            ->add('endDate', new DaterangepickerType(), array(
                'label' => 'End',
//                'data' => $options['data']->getEndDate(),
                'model_timezone' => 'UTC',
                'view_timezone' => $this->datetime->getUserTimezone(),
                'widget' => 'single_text',
                'format' => $this->datetime->getUserDatetimeFormat(),
                'date_format' => $this->datetime->getUserDateFormat(),
                'input' => 'datetime',
//                'read_only' => true,
                'attr' => array(
//                    'placeholder' => 'Will be filled automatically',
                    'is_end_date' => true,
                    'input_group' => array(
                        'append' => '<span class="fa fa-calendar">',
                    ),
                    // TODO: Decide how to display/deal with user timezone vs. campaign timezone.
//                    'help_text' => 'Timezone: '.$options['data']->getEndDate()->getTimezone()->getName(),
                )
            ))
            ->add('timezone', 'hidden', array(
                'data' => $this->datetime->getUserTimezone(),
            ));
    }

    public function getName()
    {
        return 'campaignchain_hook_campaignchain_duration';
    }
}