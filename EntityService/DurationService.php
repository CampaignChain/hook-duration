<?php
/*
 * This file is part of the CampaignChain package.
 *
 * (c) Sandro Groganz <sandro@campaignchain.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CampaignChain\Hook\DurationBundle\EntityService;

use CampaignChain\CoreBundle\EntityService\HookServiceTriggerInterface;
use CampaignChain\Hook\DurationBundle\Entity\Duration;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DurationService implements HookServiceTriggerInterface
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $em;
    }

//    public function newObject($entityClass, $entityId, $formData){
//        $duration = new Duration();
//        $duration->setEntityId($entityId);
//        $duration->setEntityClass($entityClass);
//        $duration->setStartDate($formData['start_date']);
//        $duration->setEndDate($formData['end_date']);
//
//        return $duration;
//    }

    public function getHook($entity){
        $hook = new Duration();

        if(is_object($entity) && $entity->getId() !== null){
            $hook->setStartDate($entity->getStartDate());
            $hook->setEndDate($entity->getEndDate());
        }

        return $hook;
    }

    public function processHook($entity, $hook){
        // Update the dates of the entity.
        $entity->setStartDate($hook->getStartDate());
        $entity->setEndDate($hook->getEndDate());

        // If the entity is an Activity and it equals the Operation,
        // then the dates should also be set for the Operation.
        $class = get_class($entity);
        if(strpos($class, 'CoreBundle\Entity\Activity') !== false && $entity->getEqualsOperation() == true){
            $operation = $entity->getOperations()[0];
            $operation->setStartDate($hook->getStartDate());
            $operation->setEndDate($hook->getEndDate());
        }

        return $entity;
    }

    /**
     * This method is being called by the scheduler to check whether
     * an entity's trigger hook allows the scheduler to execute
     * the entity's Job.
     *
     * @param $entity
     * @return bool
     */
    public function isExecutable($entity){
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if($entity->getStartDate() <= $now && $entity->getEndDate() <= $now){
            return true;
        }

        return false;
    }

    public function arrayToObject($hookData){
        if(is_array($hookData) && count($hookData)){
            $hook = new Duration();
            foreach($hookData as $property => $value){
                // TODO: Research whether this is a security risk, e.g. if the property name has been injected via a REST post.
                $method = 'set'.Inflector::classify($property);
                if(($method == 'setStartDate' || $method == 'setEndDate') && !is_object($value) && !$value instanceof \DateTime){
                    $value = new \DateTime($value);
                }
                $hook->$method($value);
            }
        }

        return $hook;
    }

    public function tplInline($entity){
        $hook = $this->getHook($entity);
        return $this->container->get('templating')->render(
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
}