
/**
 * Watcher panel
 *
 * @module package/quiqqer/watcher/bin/Panel
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/watcher/bin/Panel', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'controls/grid/Grid',
    'Locale',
    'Ajax'

], function(QUI, QUIPanel, Grid, QUILocale, QUIAjax)
{
    "use strict";

    var lg = 'quiqqer/watcher';

    return new Class({

        Extends : QUIPanel,
        Type : 'package/quiqqer/watcher/bin/Panel',

        Binds : [
            'loadData',
            '$onCreate',
            '$onResize'
        ],

        initialize : function(option)
        {
            this.setAttributes({
                icon : 'icon-eye-open',
                title : QUILocale.get('quiqqer/qatcher', 'panel.title')
            });

            this.parent(option);

            this.$Grid = null;

            this.addEvents({
                onCreate : this.$onCreate,
                onResize : this.$onResize,
                onInject : this.$onInject
            });
        },

        /**
         * event : on create
         */
        $onCreate : function()
        {
            // Buttons

            // Grid
            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                columnModel : [{
                    header    : QUILocale.get('quiqqer/system', 'id'),
                    dataIndex : 'id',
                    dataType  : 'number',
                    width     : 60
                }, {
                    header    : QUILocale.get('quiqqer/system', 'user_id'),
                    dataIndex : 'uid',
                    dataType  : 'number',
                    width     : 100
                }, {
                    header    : QUILocale.get('quiqqer/system', 'username'),
                    dataIndex : 'username',
                    dataType  : 'number',
                    width     : 100
                }, {
                    header    : QUILocale.get('quiqqer/system', 'date'),
                    dataIndex : 'statusTime',
                    dataType  : 'date',
                    width     : 140
                }, {
                    header    : QUILocale.get(lg, 'grid.message'),
                    dataIndex : 'message',
                    dataType  : 'string',
                    width     : 300
                }, {
                    header    : QUILocale.get(lg, 'grid.call'),
                    dataIndex : 'call',
                    dataType  : 'string',
                    width     : 200
                }, {
                    header    : QUILocale.get(lg, 'grid.callParams'),
                    dataIndex : 'callParams',
                    dataType  : 'string',
                    width     : 200
                }],
                onrefresh  : this.loadData,
                pagination : true,
                serverSort : true,
                sortOn     : 'statusTime',
                sortBy     : 'DESC'
            });
        },

        /**
         * event : on inject
         */
        $onInject : function()
        {
            this.loadData();
        },

        /**
         * event : on resize
         */
        $onResize : function()
        {
            if (!this.$Grid) {
                return;
            }

            var Body = this.getContent();

            if (!Body) {
                return;
            }


            var size = Body.getSize();

            this.$Grid.setHeight(size.y - 40);
            this.$Grid.setWidth(size.x - 40);
        },

        /**
         * Load the grid data
         */
        loadData : function()
        {
            if (!this.$Grid) {
                return;
            }

            this.Loader.show();

            var self = this;
            var options = this.$Grid.options;

            QUIAjax.get('package_quiqqer_watcher_ajax_list', function(result)
            {
                self.$Grid.setData(result);
                self.Loader.hide();

            }, {
                'package' : 'quiqqer/watcher',
                params : JSON.encode({
                    sortOn  : options.sortOn,
                    sortBy  : options.sortBy,
                    perPage : options.perPage,
                    page    : options.page
                })
            });
        }
    });

});
