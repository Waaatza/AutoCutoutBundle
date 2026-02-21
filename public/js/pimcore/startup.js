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
                            console.log(`[WatzaAutoCutout] Button gedrückt für Asset-ID: ${asset.id}`);

                            Ext.Ajax.request({
                                url: '/admin/watza/autocutout/remake',
                                method: 'POST',
                                params: {
                                    id: asset.id,
                                    fuzz: 0.15
                                },
                                success: (response) => {
                                    const data = Ext.decode(response.responseText);
                                    if (data.success) {
                                        console.log(`[WatzaAutoCutout] Freistellen erfolgreich gestartet für Asset-ID ${asset.id}`);
                                        Ext.Msg.alert('Erfolg', 'Bild wird erneut freigestellt.');
                                    } else {
                                        console.warn(`[WatzaAutoCutout] Fehler: ${data.message}`);
                                        Ext.Msg.alert('Fehler', data.message);
                                    }
                                },
                                failure: (response) => {
                                    console.error(`[WatzaAutoCutout] AJAX Fehler für Asset-ID ${asset.id}:`, response);
                                    Ext.Msg.alert('Fehler', 'Server konnte nicht erreicht werden.');
                                }
                            });
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