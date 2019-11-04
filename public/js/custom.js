var Player = /** @class */ (function () {
    function Player(audioFile, progressBar, playbackRate) {
        var self = this;

        this.progressBar = progressBar;
        this.rate        = typeof b === 'undefined' ? playbackRate : 1;
        this.sound       = new Howl(
            {
                // use given rate
                rate: self.rate,

                src: audioFile,

                // remove pitch from fast/slow playpack rate
                html5: true,

                // when sound plays
                onplay: function() {
                    requestAnimationFrame(self.step.bind(self));
                },

                // when sound ends
                onend: function() {
                    this.play();
                },

                onpause: function() {},

                onstop: function() {},

                onseek: function() {
                    requestAnimationFrame(self.step.bind(self));
                }
            }
        );
    }

    Player.prototype.togglePlay = function () {
        var playing = this.sound.playing();

        if (playing) {
            this.pause();
        } else {
            this.play();
        }

        return !playing;
    };

    Player.prototype.play = function () {
        this.sound.play();
    };

    Player.prototype.pause = function () {
        this.sound.pause();
    };

    Player.prototype.stop = function () {
        this.sound.stop();

        $(this.progressBar).css(
            {
                'width': '0%'
            }
        );
    };

    Player.prototype.setRate = function (rate) {
        this.sound.rate(rate);
    };

    Player.prototype.increasePosition = function (inc) {
        this.sound.seek(this.sound.seek() + inc > this.sound.duration() ? 0 : this.sound.seek() + inc);
    };

    Player.prototype.decreasePosition = function (dec) {
        this.sound.seek(this.sound.seek() < dec ? 0 : this.sound.seek() - dec);
    };

    Player.prototype.step = function () {
        var seek = this.sound.seek() || 0;

        $(this.progressBar).css(
            {
                'width': ((seek / this.sound.duration()) * 100 || 0) + '%'
            }
        );

        if (this.sound.playing()) {
            requestAnimationFrame(this.step.bind(this));
        }
    };

    Player.prototype.getPos = function () {
        return this.sound.seek() || 0;
    };

    Player.prototype.setPos = function (pos) {
        return this.sound.seek(this.sound.duration() / 100 * pos);
    };

    return Player;
}());

var Loader = (function () {
    return {
        show: function () {
            // show loading animation
            $('body').append('<div class="loading" data-target="form_register-loader">Loading&#8230;</div>');
        },

        hide: function () {
            // remove loading animation
            $('body').find('[data-target="form_register-loader"]').remove();
        }
    };
})();

var Study = (function () {
    var player = {};
    var rate = 1;

    var currentPopper = {};

    const POS_JUMP = 2;

    /**
     * Plays sound
     */
    function playerPlayIcon() {
        $('#action-toggle-play').find('.oi').removeClass('oi-media-play');
        $('#action-toggle-play').find('.oi').addClass('oi-media-pause');
    }

    /**
     * Pauses sound
     */
    function playerPauseIcon() {
        $('#action-toggle-play').find('.oi').removeClass('oi-media-pause');
        $('#action-toggle-play').find('.oi').addClass('oi-media-play');
    }

    /**
     * Toggles player state (pause/play)
     */
    function togglePlayerState() {
        var isPlaying = player.togglePlay();

        if (isPlaying) {
            playerPlayIcon();
        } else {
            playerPauseIcon();
        }
    }

    /**
     * Remove focus (tooltip and highlight) on word
     */
    function unfocusWord() {
        $('.word').removeClass('word-active');

        if (typeof currentPopper.destroy === 'function') {
            currentPopper.destroy();
        }

        $('.tools').hide();
    }

    /**
     * Set keyboard shortcuts
     */
    function setKeyboardShortcuts() {
        // toggle play
        hotkeys('space', function(e, handler) {
            e.preventDefault();

            togglePlayerState();
        });

        // increase position
        hotkeys('up', function(e, handler) {
            e.preventDefault();

            rate += 0.2;

            player.setRate(rate);
        });

        // increase position
        hotkeys('down', function(e, handler) {
            e.preventDefault();

            rate -= 0.2;

            player.setRate(rate);
        });

        // increase position
        hotkeys('right', function(e, handler) {
            player.increasePosition(POS_JUMP);
        });

        // decrease position
        hotkeys('left', function(e, handler) {
            player.decreasePosition(POS_JUMP);
        });

        // decrease position
        hotkeys('esc', function(e, handler) {
            unfocusWord();
        });
    }

    /**
     * Set event handlers
     */
    function setEvents() {
        setKeyboardShortcuts();

        $(document).on('click', '#action-toggle-play', function () {
            togglePlayerState();
        });

        // jump x seconds to the right
        $(document).on('click', '#action-jump-right', function () {
            player.increasePosition(POS_JUMP);
        });

        // jump x seconds to the left
        $(document).on('click', '#action-jump-left', function () {
            player.decreasePosition(POS_JUMP);
        });

        // set progress bar to position clicked
        $(document).on('click', '#progress-bar-wrapper', function (e) {
            player.setPos(100 / $(window).width() * e.clientX);
        });

        $(document).on('click', 'span.word', function (e) {
            $('.word').removeClass('word-active');
            $(this).addClass('word-active');

            $('.tools').show();

            currentPopper = new Popper(
                e.currentTarget,
                $('.tools')[0],
                {
                    placement: 'top'
                }
            );

            // set translation input to translation of current element
            $('[name="word-translation"]').val($(e.currentTarget).attr('data-translation'));
        });

        $(document).on('mouseup', function (e) {
            var container = $('.tools');

            // if the target of the click isn't the container nor a descendant of the container
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                unfocusWord();
            }
        });

        $(document).on('click', '[name="text-word-color"]', function () {
            var id = $(currentPopper.reference).attr('data-atomid');
            var color_id = $(this).val();

            $(currentPopper.reference).removeClass('color-1 color-2 color-3 color-4 color-5');

            if (color_id === '0') {
                var request = $.ajax({
                    url: '/study/action/color-remove',
                    method: 'POST',
                    data: {
                        id:    id
                    }
                });
            } else {
                $(currentPopper.reference).addClass('color-' + color_id);

                var request = $.ajax({
                    url: '/study/action/color',
                    method: 'POST',
                    data: {
                        id:    id,
                        color: color_id
                    }
                });

                request.done(function(msg) {});
            }
        });

        $(document).on('click', '#add-translation', function () {
            var id = $(currentPopper.reference).attr('data-atomid');
            var translation = $('[name="word-translation"]').val();

            $(currentPopper.reference).attr('data-translation', translation);

            if (translation.length > 0) {
                $(currentPopper.reference).addClass('translation-underline');
            } else {
                $(currentPopper.reference).removeClass('translation-underline');
            }

            var request = $.ajax({
                url: '/study/action/translation',
                method: 'POST',
                data: {
                    id:          id,
                    translation: translation
                }
            });

            request.done(function(msg) {});
        });
    }

    return {
        init: function (audioFile) {
            player = new Player('/audio/' + audioFile, '#progress-bar');

            setEvents();
        }
    }
})();

var NewTextPage = (function () {
    function sendForm(e) {
        var formData = new FormData(e.currentTarget);

        $.ajax({
            url: $(e.currentTarget).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                if (data.success === 1) {
                    window.location.href = '/text/all'
                } else {
                    $('#new-text-error').text(data.msg)
                    $('#new-text-error').attr('style', 'display: block !important;');
                }

                Loader.hide();
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    return {
        init: function () {
            $('form#new-text').submit(function(e) {
                e.preventDefault();

                Loader.show();

                sendForm(e);
            });
        }
    }
})();

var EditTextPage = (function () {
    function sendForm(e) {
        var formData = new FormData(e.currentTarget);

        $.ajax({
            url: $(e.currentTarget).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                if (data.success === 1) {
                    window.location.href = '/text/all'
                } else {
                    $('#new-text-error').text(data.msg)
                    $('#new-text-error').attr('style', 'display: block !important;');
                }

                Loader.hide();
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    return {
        init: function () {
            $('form#new-text').submit(function(e) {
                e.preventDefault();

                Loader.show();

                sendForm(e);
            });
        }
    }
})();

var NewLanguagePage = (function () {
    function sendForm(e) {
        var formData = new FormData(e.currentTarget);

        $.ajax({
            url: $(e.currentTarget).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                if (data.success === 1) {
                    window.location.href = '/language/all'
                } else {
                    $('#new-language-error').text(data.msg)
                    $('#new-language-error').attr('style', 'display: block !important;');
                }

                Loader.hide();
            },
            cache: false,
            contentType: false,
            processData: false
        });
    }

    return {
        init: function () {
            $('form#new-language').submit(function(e) {
                e.preventDefault();

                Loader.show();

                sendForm(e);
            });
        }
    }
})();
