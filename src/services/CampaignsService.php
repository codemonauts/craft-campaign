<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\services;

use craft\base\Component;
use craft\mail\Message;
use DateTime;
use putyourlightson\campaign\Campaign;
use putyourlightson\campaign\elements\CampaignElement;
use putyourlightson\campaign\elements\ContactElement;
use putyourlightson\campaign\elements\SendoutElement;
use putyourlightson\campaign\helpers\SettingsHelper;
use putyourlightson\campaign\models\ContactCampaignModel;
use putyourlightson\campaign\records\CampaignRecord;
use putyourlightson\campaign\records\ContactCampaignRecord;
use putyourlightson\campaign\records\LinkRecord;

class CampaignsService extends Component
{
    /**
     * Returns a campaign by ID.
     */
    public function getCampaignById(int $campaignId): ?CampaignElement
    {
        /** @var CampaignElement|null */
        return CampaignElement::find()
            ->id($campaignId)
            ->site('*')
            ->status(null)
            ->one();
    }

    /**
     * Adds a contact interaction.
     */
    public function addContactInteraction(ContactElement $contact, SendoutElement $sendout, string $interaction, LinkRecord $linkRecord = null): void
    {
        // Ensure that interaction exists
        if (!in_array($interaction, ContactCampaignModel::INTERACTIONS)) {
            return;
        }

        /** @var CampaignRecord|null $campaignRecord */
        $campaignRecord = CampaignRecord::find()
            ->where(['id' => $sendout->campaignId])
            ->with('campaignType')
            ->one();

        if ($campaignRecord === null) {
            return;
        }

        /** @var ContactCampaignRecord|null $contactCampaignRecord */
        $contactCampaignRecord = ContactCampaignRecord::find()
            ->where([
                'contactId' => $contact->id,
                'sendoutId' => $sendout->id,
            ])
            ->one();

        if ($contactCampaignRecord === null) {
            return;
        }

        // If first time for this interaction
        if ($contactCampaignRecord->{$interaction} === null) {
            $contactCampaignRecord->{$interaction} = new DateTime();
            $campaignRecord->{$interaction}++;
        }

        // If opened
        if ($interaction == 'opened') {
            $contactCampaignRecord->opens = $contactCampaignRecord->opens ? $contactCampaignRecord->opens + 1 : 1;
            $campaignRecord->opens++;
        }
        // If clicked
        elseif ($interaction == 'clicked') {
            // If not yet opened
            if ($contactCampaignRecord->opened === null) {
                $contactCampaignRecord->opened = new DateTime();
                $contactCampaignRecord->opens = 1;
                $campaignRecord->opened++;
                $campaignRecord->opens++;
            }

            // Increment clicks
            $contactCampaignRecord->clicks = $contactCampaignRecord->clicks ? $contactCampaignRecord->clicks + 1 : 1;
            $campaignRecord->clicks++;

            // If link record exists
            if ($linkRecord !== null) {
                // Increment clicks
                $linkRecord->clicks = $linkRecord->clicks ? $linkRecord->clicks + 1 : 1;

                // Increment clicked if first link click for this contact
                if (!in_array($linkRecord->id, explode(',', $contactCampaignRecord->links))) {
                    $linkRecord->clicked = $linkRecord->clicked ? $linkRecord->clicked + 1 : 1;
                }

                // Append link ID
                $contactCampaignRecord->links = $contactCampaignRecord->links ? $contactCampaignRecord->links . ',' . $linkRecord->id : $linkRecord->id;

                $linkRecord->save();
            }
        }

        // Only save if anonymous tracking is not enabled
        if (!Campaign::$plugin->settings->enableAnonymousTracking) {
            $contactCampaignRecord->save();
        }

        $campaignRecord->save();
    }

    /**
     * Sends a test.
     */
    public function sendTest(CampaignElement $campaign, ContactElement $contact): bool
    {
        // Get body
        $htmlBody = $campaign->getHtmlBody($contact);
        $plaintextBody = $campaign->getPlaintextBody($contact);

        // Get from name and email
        $fromNameEmail = SettingsHelper::getFromNameEmail($campaign->siteId);

        // Compose message
        /** @var Message $message*/
        $message = Campaign::$plugin->mailer->compose()
            ->setFrom([$fromNameEmail['email'] => $fromNameEmail['name']])
            ->setTo($contact->email)
            ->setSubject('[Test] ' . $campaign->title)
            ->setHtmlBody($htmlBody)
            ->setTextBody($plaintextBody);

        if ($fromNameEmail['replyTo']) {
            $message->setReplyTo($fromNameEmail['replyTo']);
        }

        return $message->send();
    }
}
