<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) CampaignChain, Inc. <info@campaignchain.com>
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

    /** @var DateTimeUtil $datetime */
    protected $datetime;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->datetime = $this->container->get('campaignchain.core.util.datetime');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('rest' == $this->view) {
            $builder->add('startDate', 'datetime', array(
                    'widget' => 'single_text',
                    'date_format' => 'Y-m-d\TH:i:sP',
                ))
                ->add('endDate', 'datetime', array(
                    'widget' => 'single_text',
                    'date_format' => 'Y-m-d\TH:i:sP',
                ))
                ->add('timezone', 'hidden', array(
                    'data' => 'UTC',
                ))
            ;
        } else {
            /*
             * If the start date and/or end date is in the past, then disable
             * the respective form field.
             */
            $now = $this->datetime->getNow();
            $disabledStartDate = false;
            $disabledEndDate = false;
            $startDateFormType = 'campaignchain_daterangepicker';
            $endDateFormType = 'campaignchain_daterangepicker';
            $helpText = 'Timezone: '.$this->datetime->getUserTimezone();

            if(
                $options['data']->getStartDate() &&
                $options['data']->getStartDate() < $now
            ){
                $disabledStartDate = true;
            }

            if(
                $options['data']->getEndDate() &&
                $options['data']->getEndDate() < $now
            ){
                $disabledStartDate = true;
                $disabledEndDate = true;
            }

            /*
             * Create the start date form field.
             */
            if($disabledStartDate) {
                $startDateFormType = 'campaignchain_datetime';
            }

            $builder
                ->add('startDate', $startDateFormType, array(
                    'label' => 'Start',
                    'disabled' => $disabledStartDate,
                    'attr' => array(
                        'placeholder' => 'Select a date range',
                        'help_text' => $helpText,
                        'input_group' => array(
                            'append' => '<span class="fa fa-calendar">',
                        ),
                    )
                ));

            /*
             * Create the end date form field.
             */
            if($disabledEndDate) {
                $endDateFormType = 'campaignchain_datetime';
            } elseif($disabledStartDate){
                $endDateFormType = 'campaignchain_datetimepicker';
            }

            if($disabledStartDate && !$disabledEndDate) {
                $builder
                    ->add('endDate', $endDateFormType, array(
                        'label' => 'End',
                        'disabled' => $disabledEndDate,
                        'start_date' => $this->datetime->formatLocale(
                                $options['data']->getStartDate()
                            ),
                        'attr' => array(
                            'help_text' => $helpText,
                            'input_group' => array(
                                'append' => '<span class="fa fa-calendar">',
                            ),
                        )
                    ));
            } else {
                $builder
                    ->add('endDate', $endDateFormType, array(
                        'label' => 'End',
                        'disabled' => $disabledEndDate,
                        'attr' => array(
                            'help_text' => $helpText,
                            'is_end_date' => true,
                            'input_group' => array(
                                'append' => '<span class="fa fa-calendar">',
                            ),
                        )
                    ));
            }
            
            $builder
                ->add('timezone', 'hidden', array(
                    'data' => $this->datetime->getUserTimezone(),
                ));
        }
    }

    public function getName()
    {
        return 'campaignchain_hook_campaignchain_duration';
    }
}