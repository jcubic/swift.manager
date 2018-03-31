/* global swift, rpc, jQuery, winamp2js */

jQuery(function($) {
    swift.then(function(swift) {
        swift.register_app('winamp', {
            label: 'winamp',
            run: function() {
                if (typeof winamp2js === 'undefined') {
                    $.getScript('apps/winamp/winamp.bundle.js').then(init);
                } else {
                    init();
                }
                function init() {
                    var filenames = localStorage.getItem('winamp_files');
                    if (filenames) {
                        load_mp3(JSON.parse(filenames)).then((tracks) => winamp(tracks));
                    } else {
                        winamp([]);
                    }
                }
                function load_mp3(filenames) {
                    localStorage.setItem('winamp_files', JSON.stringify(filenames));
                    return Promise.all(filenames.map(function(filename) {
                        return fetch('lib/download.php?' + $.param({
                            token: swift.token,
                            filename: filename
                        })).then(function(response) {
                            return response.blob();
                        }).then(function(blob) {
                            return {
                                blob: blob,
                                defaultName: filename.replace(/^.*\//, '')
                            }
                        });
                    })).catch(function(e) {
                        console.error(e);
                    });
                }
                function winamp(tracks) {
                    const winamp = new winamp2js({
                        initialTracks: tracks,
                        initialSkin: {
                            url: "apps/winamp/skins/XMMS-Turquoise-46ab165b574d97e2e51b22cb47a7661c.wsz"
                        },
                        filePickers: [
                            {
                                contextMenuName: "server file...",
                                filePicker: async () => {
                                    return await swift.open('/home/kuba/mp3/', /.*mp3$/, load_mp3);
                                },
                                requiresNetwork: true
                            }
                        ],
                        enableHotkeys: false // Enable hotkeys
                    });
                    window.winamp = winamp;
                    // Render after the skin has loaded.
                    winamp.renderWhenReady($('<div/>').appendTo('body')[0]);
                }
            }
        });
    });
});
