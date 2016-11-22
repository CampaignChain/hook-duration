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

namespace CampaignChain\Hook\DurationBundle\Entity;

class Duration
{
    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var \DateTime
     */
    protected $endDate;

    protected $timezone = 'UTC';

    /**
     * A date prior to the start date which limits how far the start date can
     * be changed to an earlier date.
     *
     * @var \DateTime
     */
    protected $preStartDateLimit;

    /**
     * A date after the start date which limits how far the start date can be
     * changed to a later date.
     *
     * @var \DateTime
     */
    protected $postStartDateLimit;

    /**
     * A date prior to the end date which limits how far the end date can
     * be changed to an earlier date.
     *
     * @var \DateTime
     */
    protected $preEndDateLimit;

    /**
     * A date after the end date which limits how far the end date can be
     * changed to a later date.
     *
     * @var \DateTime
     */
    protected $postEndDateLimit;

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Duration
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Duration
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return Duration
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return \DateTime
     */
    public function getPreStartDateLimit()
    {
        return $this->preStartDateLimit;
    }

    /**
     * @param \DateTime|null $preStartDateLimit
     * @throws \Exception
     */
    public function setPreStartDateLimit(\DateTime $preStartDateLimit = null)
    {
        if($preStartDateLimit && $preStartDateLimit > $this->startDate){
            throw new \Exception(
                'Pre start date limit ('.$preStartDateLimit->format(\DateTime::ISO8601).')'.
                'must be earlier than start date ('.$this->startDate->format(\DateTime::ISO8601).')'.
                '.'
            );
        }
        $this->preStartDateLimit = $preStartDateLimit;
    }

    /**
     * @return \DateTime
     */
    public function getPostStartDateLimit()
    {
        return $this->postStartDateLimit;
    }

    /**
     * @param \DateTime $postStartDateLimit
     * @throws \Exception
     */
    public function setPostStartDateLimit(\DateTime $postStartDateLimit = null)
    {
        if($postStartDateLimit && $postStartDateLimit < $this->startDate){
            throw new \Exception(
                'Post start date limit ('.$postStartDateLimit->format(\DateTime::ISO8601).')'.
                'must be later than start date ('.$this->startDate->format(\DateTime::ISO8601).')'.
                '.'
            );
        }
        $this->postStartDateLimit = $postStartDateLimit;
    }

    /**
     * @return \DateTime
     */
    public function getPreEndDateLimit()
    {
        return $this->preEndDateLimit;
    }

    /**
     * @param \DateTime $preEndDateLimit
     * @throws \Exception
     */
    public function setPreEndDateLimit(\DateTime $preEndDateLimit = null)
    {
        if($preEndDateLimit && $preEndDateLimit > $this->endDate){
            throw new \Exception(
                'Pre end date limit ('.$preEndDateLimit->format(\DateTime::ISO8601).')'.
                'must be earlier than end date ('.$this->endDate->format(\DateTime::ISO8601).')'.
                '.'
            );
        }
        $this->preEndDateLimit = $preEndDateLimit;
    }

    /**
     * @return \DateTime
     */
    public function getPostEndDateLimit()
    {
        return $this->postEndDateLimit;
    }

    /**
     * @param \DateTime $postEndDateLimit
     * @throws \Exception
     */
    public function setPostEndDateLimit(\DateTime $postEndDateLimit = null)
    {
        if($postEndDateLimit && $postEndDateLimit > $this->endDate){
            throw new \Exception(
                'Post end date limit ('.$postEndDateLimit->format(\DateTime::ISO8601).')'.
                'must be later than end date ('.$this->endDate->format(\DateTime::ISO8601).')'.
                '.'
            );
        }
        $this->postEndDateLimit = $postEndDateLimit;
    }
}
