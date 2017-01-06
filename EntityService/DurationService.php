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

namespace CampaignChain\Hook\DurationBundle\EntityService;

use CampaignChain\CoreBundle\Entity\Action;
use CampaignChain\CoreBundle\Entity\Hook;
use CampaignChain\CoreBundle\EntityService\CampaignService;
use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\CoreBundle\Exception\ErrorCode;
use CampaignChain\Hook\DurationBundle\Entity\Duration;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use CampaignChain\CoreBundle\Entity\Campaign;

class DurationService extends HookServiceTriggerInterface
{
    protected $em;
    protected $templating;
    protected $campaignService;

    public function __construct(
        ManagerRegistry $managerRegistry,
        CampaignService $campaignService,
        EngineInterface $templating
    ){
        $this->em = $managerRegistry->getManager();
        $this->campaignService = $campaignService;
        $this->templating = $templating;
    }

    /**
     * @param Action $entity
     * @param string $mode
     * @return Duration
     */
    public function getHook($entity, $mode = Hook::MODE_DEFAULT){
        if(is_object($entity) && $entity->getId() !== null){
            $class = get_class($entity);
            
            /*
             * If this is a Campaign, then we set the limits for the start and
             * end date as per the first and last Activity or Milestone
             * contained in the campaign.
             */
            if(strpos($class, 'CoreBundle\Entity\Campaign') !== false) {
                $entity = $this->setPostStartDateLimit($entity);
                $entity = $this->setPreEndDateLimit($entity);
            }
        }

        $hook = new Duration();
        $hook->setStartDate($entity->getStartDate());
        $hook->setEndDate($entity->getEndDate());
        $hook->setPreStartDateLimit($entity->getPreStartDateLimit());
        $hook->setPostStartDateLimit($entity->getPostStartDateLimit());
        $hook->setPreEndDateLimit($entity->getPreEndDateLimit());
        $hook->setPostEndDateLimit($entity->getPostEndDateLimit());

        return $hook;
    }

    public function processHook($entity, $hook){
        $hookStartDate = $hook->getStartDate();
        $hookEndDate = $hook->getEndDate();

        $class = get_class($entity);

        if(strpos($class, 'CoreBundle\Entity\Campaign') !== false) {
            // Check new start date.
            if(!$this->campaignService->isValidStartDate($entity, $hook->getStartDate())){
                $this->addErrorCode(ErrorCode::CAMPAIGN_CONCURRENT_EDIT_START_DATE);
                $entity = $this->setPostStartDateLimit($entity);
                if($entity->getPostStartDateLimit()) {
                    $hookStartDate = $entity->getPostStartDateLimit();
                }
            }

            // Check new end date.
            if(!$this->campaignService->isValidEndDate($entity, $hook->getEndDate())){
                $this->addErrorCode(ErrorCode::CAMPAIGN_CONCURRENT_EDIT_END_DATE);
                $entity = $this->setPreEndDateLimit($entity);
                if($entity->getPreEndDateLimit()) {
                    $hookEndDate = $entity->getPreEndDateLimit();
                }
            }
        }

        // Update the dates of the entity.
        $entity->setStartDate($hookStartDate);
        $entity->setEndDate($hookEndDate);

        // If the entity is an Activity and it equals the Operation,
        // then the dates should also be set for the Operation.
        if(strpos($class, 'CoreBundle\Entity\Activity') !== false && $entity->getEqualsOperation() == true){
            $operation = $entity->getOperations()[0];
            $operation->setStartDate($hookStartDate);
            $operation->setEndDate($hookEndDate);
        }

        $this->setEntity($entity);

        if($this->hasErrors()){
            return false;
        }

        return true;
    }

    public function arrayToObject($hookData){
        if(is_array($hookData) && count($hookData)){
            $hook = new Duration();
            foreach($hookData as $property => $value){
                // TODO: Research whether this is a security risk, e.g. if the property name has been injected via a REST post.
                $method = 'set'.Inflector::classify($property);
                if(($method == 'setStartDate' || $method == 'setEndDate') && !is_object($value) && !$value instanceof \DateTime){
                    $value = new \DateTime($value, new \DateTimeZone($hookData['timezone']));
                }
                $hook->$method($value);
            }
        }

        return $hook;
    }

    public function tplInline($entity){
        $hook = $this->getHook($entity);
        return $this->templating->render(
            'CampaignChainHookDurationBundle::inline.html.twig',
            array('hook' => $hook)
        );
    }

    /**
     * Returns the corresponding start date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getStartDateIdentifier(){
        return 'startDate';
    }

    /**
     * Returns the corresponding end date field attribute name as specified in the respective form type.
     *
     * @return string
     */
    public function getEndDateIdentifier(){
        return 'endDate';
    }

    public function setPostStartDateLimit($entity)
    {
        /** @var Action $firstAction */
        $firstAction = $this->em->getRepository('CampaignChain\CoreBundle\Entity\Campaign')
            ->getFirstAction($entity);

        if ($firstAction) {
            try {
                $entity->setPostStartDateLimit($firstAction->getStartDate());
            } catch(\Exception $e) {
                $entity->setStartDate($firstAction->getStartDate());
            }
        }

        return $entity;
    }

    public function setPreEndDateLimit($entity)
    {
        /** @var Action $lastAction */
        $lastAction = $this->em->getRepository('CampaignChain\CoreBundle\Entity\Campaign')
            ->getLastAction($entity);

        if ($lastAction) {
            if (!$lastAction->getEndDate()) {
                $preEndDateLimit = $lastAction->getStartDate();
            } else {
                $preEndDateLimit = $lastAction->getEndDate();
            }

            try {
                $entity->setPreEndDateLimit($preEndDateLimit);
            } catch(\Exception $e) {
                $entity->setEndDate($preEndDateLimit);
            }
        }

        return $entity;
    }
}