<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\models;

use craft\base\Model;
use DateTime;
use putyourlightson\campaign\Campaign;
use putyourlightson\campaign\elements\ContactElement;
use putyourlightson\campaign\elements\MailingListElement;

/**
 * @property-read null|ContactElement $contact
 * @property-read string $interaction
 * @property-read null|MailingListElement $mailingList
 */
class ContactMailingListModel extends Model
{
    /**
     * @const array
     */
    public const INTERACTIONS = ['subscribed', 'unsubscribed', 'complained', 'bounced'];

    /**
     * @var int|null ID
     */
    public ?int $id;

    /**
     * @var int|null Contact ID
     */
    public ?int $contactId;

    /**
     * @var int|null Mailing list ID
     */
    public ?int $mailingListId;

    /**
     * @var string Subscription status
     */
    public string $subscriptionStatus;

    /**
     * @var DateTime|null Subscribed
     */
    public ?DateTime $subscribed;

    /**
     * @var DateTime|null Unsubscribed
     */
    public ?DateTime $unsubscribed;

    /**
     * @var DateTime|null Complained
     */
    public ?DateTime $complained;

    /**
     * @var DateTime|null Bounced
     */
    public ?DateTime $bounced;

    /**
     * @var DateTime|null Verified
     */
    public ?DateTime $verified;

    /**
     * @var string Source type
     */
    public string $sourceType = '';

    /**
     * @var string Source
     */
    public string $source = '';

    /**
     * @var DateTime
     */
    public DateTime $dateUpdated;

    /**
     * Returns the contact.
     */
    public function getContact(): ?ContactElement
    {
        return Campaign::$plugin->contacts->getContactById($this->contactId);
    }

    /**
     * Returns the mailing list.
     */
    public function getMailingList(): ?MailingListElement
    {
        return Campaign::$plugin->mailingLists->getMailingListById($this->mailingListId);
    }

    /**
     * Returns the most significant interaction.
     */
    public function getInteraction(): string
    {
        $interactions = ['bounced', 'complained', 'unsubscribed', 'subscribed'];

        foreach ($interactions as $interaction) {
            if ($this->{$interaction} !== null) {
                return $interaction;
            }
        }

        return '';
    }
}
