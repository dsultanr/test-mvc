
Ext.Loader.setConfig({enabled: true});

Ext.Loader.setPath('Ext.ux', '../ux/');
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.util.*',
    'Ext.grid.plugin.BufferedRenderer',
    'Ext.ux.form.SearchField'
]);

Ext.onReady(function(){
    Ext.define('Logs', {
        extend: 'Ext.data.Model',
        fields: [{
            name: 'ip',
        }, {
            name: 'os',
        }, {
            name: 'browser',
        }, {
            name: 'lasturl',
        }, {
            name: 'firstref',
        }, {
            name: 'discurlcount',
            type: 'int',
        },
        ],
        idProperty: 'id'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.Store', {
        id: 'store',
        model: 'Logs',
        pageSize: 50,
        proxy: {
            type: 'ajax',
            url: '?controller=logs&action=jsondata',
            reader: {
                root: 'logs',
                totalProperty: 'totalCount'
            },
            filterParam: 'ip',
            encodeFilters: function(filters) {
                return filters[0].value;
            }
        },
        remoteFilter: true,
        remoteSort: true,
        autoLoad: true
    });

    function onStoreSizeChange() {
        grid.down('#status').update({count: store.getTotalCount()});
    }

    var grid = Ext.create('Ext.grid.Panel', {
        title: 'Logs',
        store: store,
        loadMask: true,
        dockedItems: [{
            dock: 'top',
            xtype: 'toolbar',
            items: [{
                width: 400,
                fieldLabel: 'Filter IP',
                labelWidth: 50,
                xtype: 'searchfield',
                store: store
            }, '->', {
                xtype: 'component',
                itemId: 'status',
                tpl: 'Matching threads: {count}',
                style: 'margin-right:5px'
            }]
        }],
        selModel: {
            pruneRemoved: false
        },
        multiSelect: true,
        viewConfig: {
            trackOver: false,
            emptyText: '<h1 style="margin:20px">No logs. Try to <a href="index.php?controller=logs&action=dbseed">Load some</a></h1>'
        },
        // grid columns
        columns:[{
            text: "IP",
            dataIndex: 'ip',
            sortable: false
        },{
            text: "OS",
            dataIndex: 'os',
            flex: 1,
            sortable: true
        },{
            text: "BROWSER",
            dataIndex: 'browser',
            flex: 1,
            sortable: true
        },{
            text: "FIRSTREF",
            dataIndex: 'firstref',
            flex: 1,
            sortable: false
        },{
            text: "LASTURL",
            dataIndex: 'lasturl',
            flex: 1,
            sortable: false
        },{
            text: "DISCURLCOUNT",
            dataIndex: 'discurlcount',
            sortable: false
        }],
        renderTo: 'mygrid',
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store
        })
    });
});