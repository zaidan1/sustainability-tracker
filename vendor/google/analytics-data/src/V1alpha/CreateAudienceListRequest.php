<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/data/v1alpha/analytics_data_api.proto

namespace Google\Analytics\Data\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A request to create a new audience list.
 *
 * Generated from protobuf message <code>google.analytics.data.v1alpha.CreateAudienceListRequest</code>
 */
class CreateAudienceListRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent resource where this audience list will be created.
     * Format: `properties/{property}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The audience list to create.
     *
     * Generated from protobuf field <code>.google.analytics.data.v1alpha.AudienceList audience_list = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $audience_list = null;

    /**
     * @param string                                      $parent       Required. The parent resource where this audience list will be created.
     *                                                                  Format: `properties/{property}`
     *                                                                  Please see {@see AlphaAnalyticsDataClient::propertyName()} for help formatting this field.
     * @param \Google\Analytics\Data\V1alpha\AudienceList $audienceList Required. The audience list to create.
     *
     * @return \Google\Analytics\Data\V1alpha\CreateAudienceListRequest
     *
     * @experimental
     */
    public static function build(string $parent, \Google\Analytics\Data\V1alpha\AudienceList $audienceList): self
    {
        return (new self())
            ->setParent($parent)
            ->setAudienceList($audienceList);
    }

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent resource where this audience list will be created.
     *           Format: `properties/{property}`
     *     @type \Google\Analytics\Data\V1alpha\AudienceList $audience_list
     *           Required. The audience list to create.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Data\V1Alpha\AnalyticsDataApi::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent resource where this audience list will be created.
     * Format: `properties/{property}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent resource where this audience list will be created.
     * Format: `properties/{property}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. The audience list to create.
     *
     * Generated from protobuf field <code>.google.analytics.data.v1alpha.AudienceList audience_list = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Analytics\Data\V1alpha\AudienceList|null
     */
    public function getAudienceList()
    {
        return $this->audience_list;
    }

    public function hasAudienceList()
    {
        return isset($this->audience_list);
    }

    public function clearAudienceList()
    {
        unset($this->audience_list);
    }

    /**
     * Required. The audience list to create.
     *
     * Generated from protobuf field <code>.google.analytics.data.v1alpha.AudienceList audience_list = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Analytics\Data\V1alpha\AudienceList $var
     * @return $this
     */
    public function setAudienceList($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Data\V1alpha\AudienceList::class);
        $this->audience_list = $var;

        return $this;
    }

}

