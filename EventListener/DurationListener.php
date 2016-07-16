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

namespace CampaignChain\Hook\DurationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DurationListener implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        );
    }

    public function onPreSubmit(FormEvent $event)
    {
//        $data = $event->getData();
//        $form = $event->getForm();
//
//        // Intercept if due date is supposed to be "now".
//        if(
//            isset($data['campaignchain_hook_campaignchain_due']['execution_choice']) &&
//            $data['campaignchain_hook_campaignchain_due']['execution_choice'] == 'now'
//        ){
//            $nowDate = new \DateTime('now');
//
//            $datetimeUtil = $this->container->get('campaignchain.core.util.datetime');
//            $data['campaignchain_hook_campaignchain_due']['date'] = $datetimeUtil->formatLocale($nowDate);
//        }
//
//        $event->setData($data);
    }
}