<?php
/*
 * Copyright 2016 CampaignChain, Inc. <info@campaignchain.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CampaignChain\Hook\DurationBundle\Form\Type;

use CampaignChain\CoreBundle\Form\Type\DateTimePickerType;
use CampaignChain\CoreBundle\Form\Type\DateTimeType;
use CampaignChain\CoreBundle\Form\Type\HookType;
use Symfony\Component\Form\FormBuilderInterface;
use CampaignChain\CoreBundle\Util\DateTimeUtil;
use CampaignChain\CoreBundle\Form\Type\DateRangePickerType;
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
        $this->setOptions($options);

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
            $readonlyStartDate = false;
            $readonlyEndDate = false;
            $startDateFormType = DateRangePickerType::class;
            $endDateFormType = DateRangePickerType::class;
            $helpText = 'Timezone: '.$this->datetime->getUserTimezone();

            if(
                $options['data']->getStartDate() &&
                $options['data']->getStartDate() < $now
            ){
                $readonlyStartDate = true;
            }

            if(
                $options['data']->getEndDate() &&
                $options['data']->getEndDate() < $now
            ){
                $readonlyStartDate = true;
                $readonlyEndDate = true;
            }

            /*
             * Create the start date form field.
             */
            if($readonlyStartDate) {
                $startDateFormType = DateTimeType::class;
            }

            if($readonlyStartDate) {
                $builder
                    ->add('startDate', $startDateFormType, array(
                        'label' => 'Start',
                        'attr' => array(
                            'readonly' => $readonlyStartDate,
                            'placeholder' => 'Select a date range',
                            'help_text' => $helpText,
                            'input_group' => array(
                                'append' => '<span class="fa fa-calendar">',
                            ),
                        )
                    ));
            } else {
                if(
                    !$options['data']->getPostStartDateLimit() &&
                    !$options['data']->getPreStartDateLimit()
                ) {
                    // There is no limit to the start date.
                    $builder
                        ->add('startDate', $startDateFormType, array(
                            'label' => 'Start',
                            'attr' => array(
                                'placeholder' => 'Select a date range',
                                'help_text' => $helpText,
                                'input_group' => array(
                                    'append' => '<span class="fa fa-calendar">',
                                ),
                            )
                        ));
                } else {
                    // There is a limit.
                    if($options['data']->getPreStartDateLimit()) {
                        $startDate = $this->datetime->formatLocale(
                            $options['data']->getPreStartDateLimit()
                        );
                    } else {
                        $startDate = $this->datetime->formatLocale(
                            $this->datetime->getNow()
                        );
                    }

                    $endDate = $this->datetime->formatLocale(
                        $options['data']->getPostStartDateLimit()
                    );

                    $builder
                        ->add('startDate', DateTimePickerType::class, array(
                            'label' => 'Start',
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'attr' => array(
                                'help_text' => $helpText,
                                'input_group' => array(
                                    'append' => '<span class="fa fa-calendar">',
                                ),
                            )
                        ));
                }
            }

            /*
             * Create the end date form field.
             */
            if($readonlyEndDate) {
                $endDateFormType = DateTimeType::class;
            } elseif(
                $readonlyStartDate ||
                $options['data']->getPostStartDateLimit() ||
                $options['data']->getPreStartDateLimit()
            ){
                $endDateFormType = DateTimePickerType::class;
            }

            if(
                !$readonlyEndDate &&
                (
                    $readonlyStartDate ||
                    $options['data']->getPostStartDateLimit() ||
                    $options['data']->getPreStartDateLimit()
                )
            ) {
                // Show date time picker.
                if($options['data']->getPreEndDateLimit()) {
                    $startDate = $this->datetime->formatLocale(
                        $options['data']->getPreEndDateLimit()
                    );
                } else {
                    $startDate = $this->datetime->formatLocale(
                        $options['data']->getStartDate()
                    );
                }

                if($options['data']->getPostEndDateLimit()) {
                    $endDate = $this->datetime->formatLocale(
                        $options['data']->getPostEndDateLimit()
                    );
                } else {
                    $endDate = null;
                }


                $builder
                    ->add('endDate', $endDateFormType, array(
                        'label' => 'End',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'attr' => array(
                            'help_text' => $helpText,
                            'input_group' => array(
                                'append' => '<span class="fa fa-calendar">',
                            ),
                        )
                    ));
            } elseif($readonlyEndDate) {
                // Show read only date time field.
                $builder
                    ->add('endDate', $endDateFormType, array(
                        'label' => 'End',
                        'attr' => array(
                            'readonly' => $readonlyEndDate,
                            'help_text' => $helpText,
                            'is_end_date' => true,
                            'input_group' => array(
                                'append' => '<span class="fa fa-calendar">',
                            ),
                        )
                    ));
            } else {
                // Show date range picker.
                $builder
                    ->add('endDate', $endDateFormType, array(
                        'label' => 'End',
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

    public function getBlockPrefix()
    {
        return 'campaignchain_hook_campaignchain_duration';
    }
}