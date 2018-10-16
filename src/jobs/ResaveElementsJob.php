<?php
/**
 * @link      https://craftcampaign.com
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\jobs;

use craft\base\Element;
use craft\events\ElementEvent;
use craft\queue\jobs\ResaveElements;
use craft\services\Elements;
use yii\base\Event;

/**
 * ResaveElementsJob
 *
 * @author    PutYourLightsOn
 * @package   Campaign
 * @since     1.4.0
 */
class ResaveElementsJob extends ResaveElements
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $siteId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Register beforeSaveElement event so we can update the element's site ID
        Event::on(Elements::class, Elements::EVENT_BEFORE_SAVE_ELEMENT, function(ElementEvent $event) {
            /** @var Element $element */
            $element = $event->element;
            $element->siteId = $this->siteId ?? $element->siteId;
        });

        parent::execute($queue);
    }
}