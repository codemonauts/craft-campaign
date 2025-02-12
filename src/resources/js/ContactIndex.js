/** global: Campaign */
/** global: Craft */
/** global: Garnish */
/**
 * ContactIndex class
 */
Campaign.ContactIndex = Craft.BaseElementIndex.extend({
    $newContactBtn: null,

    init: function(elementType, $container, settings) {
        this.on('selectSource', this.updateSource.bind(this));
        this.base(elementType, $container, settings);
    },

    afterInit: function() {
        this.addNewButton();
        this.base();
    },

    updateSource: function() {
        if (!this.$source) {
            return;
        }

        // Get the ID of the selected source
        const selectedSourceId = this.$source.data('id');

        // Update the URL if we're on the Contacts index
        // ---------------------------------------------------------------------

        if (this.settings.context == 'index' && typeof history !== 'undefined') {
            let uri = 'campaign/contacts/view';

            if (selectedSourceId) {
                uri += '/' + selectedSourceId;
            }

            const url = Craft.getUrl(uri, document.location.search + document.location.hash);
            history.replaceState({}, '', url);
        }
    },

    addNewButton: function() {
        this.$newContactBtn = Craft.ui.createButton({
                label: Craft.t('campaign', 'New contact'),
                spinner: true,
            })
            .addClass('submit add icon btngroup-btn-last');

        this.addListener(this.$newContactBtn, 'click', () => {
            this._createContact();
        });

        this.addButton(this.$newContactBtn);
    },

    _createContact: function() {
        if (this.$newContactBtn.hasClass('loading')) {
            console.warn('New contact creation already in progress.');
            return;
        }

        this.$newContactBtn.addClass('loading');

        Craft.sendActionRequest('POST', 'elements/create', {
            data: {
                elementType: this.elementType,
            },
        }).then(ev => {
            if (this.settings.context === 'index') {
                document.location.href = Craft.getUrl(ev.data.cpEditUrl, {fresh: 1});
            }
            else {
                const slideout = Craft.createElementEditor(this.elementType, {
                    elementId: ev.data.element.id,
                    draftId: ev.data.element.draftId,
                    params: {
                        fresh: 1,
                    },
                });
                slideout.on('submit', () => {
                    this.selectElementAfterUpdate(ev.data.element.id);
                    this.updateElements();
                });
            }
        }).finally(() => {
            this.$newContactBtn.removeClass('loading');
        });
    },
});

// Register it!
Craft.registerElementIndexClass('putyourlightson\\campaign\\elements\\ContactElement', Campaign.ContactIndex);
