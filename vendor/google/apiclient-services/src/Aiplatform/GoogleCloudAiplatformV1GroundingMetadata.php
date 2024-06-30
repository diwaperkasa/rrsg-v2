<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Aiplatform;

class GoogleCloudAiplatformV1GroundingMetadata extends \Google\Collection
{
  protected $collection_key = 'webSearchQueries';
  protected $searchEntryPointType = GoogleCloudAiplatformV1SearchEntryPoint::class;
  protected $searchEntryPointDataType = '';
  /**
   * @var string[]
   */
  public $webSearchQueries;

  /**
   * @param GoogleCloudAiplatformV1SearchEntryPoint
   */
  public function setSearchEntryPoint(GoogleCloudAiplatformV1SearchEntryPoint $searchEntryPoint)
  {
    $this->searchEntryPoint = $searchEntryPoint;
  }
  /**
   * @return GoogleCloudAiplatformV1SearchEntryPoint
   */
  public function getSearchEntryPoint()
  {
    return $this->searchEntryPoint;
  }
  /**
   * @param string[]
   */
  public function setWebSearchQueries($webSearchQueries)
  {
    $this->webSearchQueries = $webSearchQueries;
  }
  /**
   * @return string[]
   */
  public function getWebSearchQueries()
  {
    return $this->webSearchQueries;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1GroundingMetadata::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1GroundingMetadata');
