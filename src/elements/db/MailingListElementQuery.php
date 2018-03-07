<?php
/**
 * @link      https://craftcampaign.com
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\campaign\elements\db;

use putyourlightson\campaign\elements\MailingListElement;
use putyourlightson\campaign\models\MailingListTypeModel;
use putyourlightson\campaign\records\MailingListTypeRecord;


use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * MailingListElementQuery
 *
 * @method MailingListElement[]|array all($db = null)
 * @method MailingListElement|array|null one($db = null)
 * @method MailingListElement|array|null nth(int $n, Connection $db = null)
 *
 * @author    PutYourLightsOn
 * @package   Campaign
 * @since     1.0.0
 */
class MailingListElementQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    /**
     * @var string MLID
     */
    public $mlid;

    /**
     * @var int|int[]|null The mailing list type ID(s) that the resulting mailing lists must have.
     */
    public $mailingListTypeId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'mailingListType':
                $this->mailingListType($value);
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Sets the [[mlid]] property.
     *
     * @param string $value The property value
     *
     * @return static self reference
     */
    public function mlid(string $value)
    {
        $this->mlid = $value;

        return $this;
    }

    /**
     * Sets the [[mailingListType]] property.
     *
     * @param string|string[]|MailingListTypeModel|null $value The property value
     *
     * @return static self reference
     */
    public function mailingListType($value)
    {
        if ($value instanceof MailingListTypeModel) {
            $this->mailingListTypeId = $value->id;
        }
        else if ($value !== null) {
            $this->mailingListTypeId = MailingListTypeRecord::find()
                ->select(['id'])
                ->where(Db::parseParam('handle', $value))
                ->column();
        }
        else {
            $this->mailingListTypeId = null;
        }

        return $this;
    }

    /**
     * Sets the [[mailingListTypeId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function mailingListTypeId($value)
    {
        $this->mailingListTypeId = $value;

        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('campaign_mailinglists');

        $this->query->select([
            'campaign_mailinglists.mlid',
            'campaign_mailinglists.mailingListTypeId',
        ]);

        if ($this->mlid) {
            $this->subQuery->andWhere(Db::parseParam('campaign_mailinglists.mlid', $this->mlid));
        }

        if ($this->mailingListTypeId) {
            $this->subQuery->andWhere(Db::parseParam('campaign_mailinglists.mailingListTypeId', $this->mailingListTypeId));
        }

        $this->subQuery->innerJoin(MailingListTypeRecord::tableName().' campaign_mailinglisttypes', 'campaign_mailinglisttypes.id = campaign_mailinglists.mailingListTypeId');
        $this->subQuery->select('campaign_mailinglisttypes.name AS mailingListType');

        if ($this->mailingListTypeId) {
            $this->subQuery->andWhere(Db::parseParam('campaign_mailinglists.mailingListTypeId', $this->mailingListTypeId));
        }

        return parent::beforePrepare();
    }
}
