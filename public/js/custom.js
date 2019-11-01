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

var TextLearning = (function () {
    var player = {};
    var rate = 1;

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
     * Set event handlers
     */
    function setEvents() {
        // toggle play
        hotkeys('space', function(e, handler) {
            e.preventDefault();

            togglePlayerState();
        });

        $(document).on('click', '#action-toggle-play', function () {
            togglePlayerState();
        })

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
