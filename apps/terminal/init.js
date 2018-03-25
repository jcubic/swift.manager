/* global swift, rpc, jQuery */

jQuery(function($) {
    swift.then(function(swift) {
        swift.register_app('terminal', {
            label: 'terminal',
            install: function() {
            },
            logout: function() {
                this.iframe[0].contentWindow.$.leash.then(function(leash) {
                    leash.terminal.logout();
                });
            },
            dialog: function() {
                return this.iframe.dialog.apply(this.iframe, arguments);
            },
            run: function() {
                console.log('run');
                var self = this;
                var iframe = this.iframe = $('<iframe/>').attr('src', './apps/terminal/leash/').on('load', function() {
                    this.contentWindow.$.leash.then(function(leash) {
                        var username = 'kuba';
                        self.leash = leash;
                        leash.terminal.autologin(username, swift.token);
                        leash.option('onDirectoryChange', function(cwd) {
                            var re = new RegExp('^' + leash.home);
                            var path = cwd.replace(re, '~');
                            iframe.dialog('option', 'title', 'Leash ' + path);
                        });
                        leash.terminal.keymap({
                            'CTRL+D': function(e, original) {
                                if (leash.terminal.level() === 1) {
                                    return false;
                                } else {
                                    return original(e);
                                }
                            }
                        });
                        //leash.set_login(username);
                    });
                }).app('terminal', {
                    title: 'Leash ',
                    minHeight: 148,
                    minWidth: 243,
                    resizeStart: function(event, ui) {
                        mask.show();
                    },
                    resizeStop: function(event, ui) {
                        mask.hide();
                    }
                });
                var mask = $('<div class="mask"/>').hide().insertAfter(iframe);
            }
        });

    });
});
