document.addEventListener(pimcore.events.postOpenAsset, (e) => {
    const asset = e.detail.asset;
    console.log(`[WatzaAutoCutout] postOpenAsset Event für Asset-ID: ${asset.id}, Name: ${asset.data.filename}`);

    let retries = 0;
    const maxRetries = 20;
    const interval = 200;

    const tryAddButton = () => {
        retries++;

        const toolbars = Ext.ComponentQuery.query('toolbar');

        console.log(`[WatzaAutoCutout] Versuch #${retries}: Gefundene Toolbars: ${toolbars.length}`);

        for (let tb of toolbars) {
            const textItem = tb.items.find(item => item.xtype === 'tbtext' && item.text === `ID ${asset.id}`);
            if (textItem) {
                console.log(`[WatzaAutoCutout] Toolbar gefunden für Asset-ID: ${asset.id}`);

                if (!tb.down('#watza_autocutout_btn')) {
                    tb.add({
                        xtype: 'button',
                        text: 'Freistellen',
                        itemId: 'watza_autocutout_btn',
                        handler: () => {

                            const previewContainer = Ext.create('Ext.Container', {
                                width: '100%',
                                height: 400,
                                layout: 'fit',
                                style: `
                                    background-color: #eee;
                                    background-image:
                                        linear-gradient(45deg, #ccc 25%, transparent 25%),
                                        linear-gradient(-45deg, #ccc 25%, transparent 25%),
                                        linear-gradient(45deg, transparent 75%, #ccc 75%),
                                        linear-gradient(-45deg, transparent 75%, #ccc 75%);
                                    background-size: 20px 20px;
                                    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
                                    `,
                                items: [{
                                    xtype: 'image',
                                    itemId: 'previewImage',
                                    style: 'max-width:100%;max-height:400px;margin:auto;'
                                }]
                            });

                            const slider = Ext.create('Ext.slider.Single', {
                                width: 300,
                                minValue: 0,
                                maxValue: 50,
                                value: 15,
                                increment: 1,
                                fieldLabel: 'Fuzz (%)',
                                listeners: {
                                    change: function (s, value) {
                                        loadPreview(value / 100);
                                    }
                                }
                            });

                            const loadPreview = (fuzz) => {
                                Ext.Ajax.request({
                                    url: '/admin/watza/autocutout/preview',
                                    method: 'POST',
                                    params: {
                                        id: asset.id,
                                        fuzz: fuzz
                                    },
                                    success: (response) => {
                                        const data = Ext.decode(response.responseText);
                                        if (data.success) {
                                            previewContainer.down('#previewImage').setSrc(data.image);
                                        }
                                    }
                                });
                            };

                            const win = Ext.create('Ext.window.Window', {
                                title: 'Freistellen Preview',
                                width: 600,
                                height: 600,
                                layout: 'vbox',
                                items: [
                                    slider,
                                    previewContainer
                                ],
                                buttons: [{
                                    text: 'Final speichern',
                                    handler: () => {
                                        Ext.Ajax.request({
                                            url: '/admin/watza/autocutout/remake',
                                            method: 'POST',
                                            params: {
                                                id: asset.id,
                                                fuzz: slider.getValue() / 100
                                            },
                                            success: () => {
                                                Ext.Msg.alert('Erfolg', 'Bild wird freigestellt.');
                                                win.close();
                                            }
                                        });
                                    }
                                }]
                            });

                            win.show();

                            loadPreview(0.15);
                        }
                    });
                    console.log(`[WatzaAutoCutout] Button erfolgreich hinzugefügt!`);
                } else {
                    console.log(`[WatzaAutoCutout] Button existiert bereits.`);
                }

                return true;
            }
        }

        console.log(`[WatzaAutoCutout] Toolbar für Asset-ID ${asset.id} noch nicht gefunden.`);
        if (retries >= maxRetries) {
            console.warn(`[WatzaAutoCutout] Max retries erreicht, Abbruch für Asset-ID ${asset.id}.`);
            return true;
        }

        return false;
    };

    const retryInterval = setInterval(() => {
        if (tryAddButton()) clearInterval(retryInterval);
    }, interval);
});