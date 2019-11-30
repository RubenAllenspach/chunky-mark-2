var Util = (function () {
    return {
        mobileAndTabletcheck: function() {
            var check = false;
            (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
            return check;
        }
    };
})();

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

                loop: true,

                // when sound plays
                onplay: function() {
                    requestAnimationFrame(self.step.bind(self));
                },

                // when sound ends
                onend: function() {},

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
     * store translation
     */
    function storeTranslation() {
        $('[name="word-translation"]').prop('disabled', true);

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

        request.done(function(msg) {
            $('[name="word-translation"]').prop('disabled', false);
        });
    }

    function colorWord(color_id) {
        var id = $(currentPopper.reference).attr('data-atomid');

        $(currentPopper.reference).removeClass('color-1 color-2 color-3 color-4 color-5');

        if (color_id === '0') {
            var request = $.ajax({
                url: '/study/action/color-remove',
                method: 'POST',
                data: {
                    id: id
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
    }

    function focusNextWord() {
        if ('reference' in currentPopper) {
            if ($(currentPopper.reference).is('span.word:last')) {
                $('span.word:first').trigger('click');
            } else {
                $(currentPopper.reference).nextAll('span.word:first').trigger('click');
            }
        } else {
            $('span.word:first').trigger('click');
        }
    }

    function focusPreviousWord() {
        if ('reference' in currentPopper) {
            if ($(currentPopper.reference).is('span.word:first')) {
                $('span.word:last').trigger('click');
            } else {
                $(currentPopper.reference).prevAll('span.word:first').trigger('click');
            }
        } else {
            $('span.word:last').trigger('click');
        }
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

        // unfocus input field or hide popper if no focus on input
        hotkeys('esc', function (e, handler) {
            unfocusWord();
        });

        // increase position
        hotkeys('ctrl+right', function(e, handler) {
            e.preventDefault();

            focusNextWord();
        });

        // decrease position
        hotkeys('ctrl+left', function(e, handler) {
            e.preventDefault();

            focusPreviousWord();
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
            colorWord($(this).val());
        });

        $(document).on('click', '#add-translation', function () {
            storeTranslation();
        });

        $(document).on('keyup', '[name="word-translation"]', function (e) {
            if (e.keyCode === 13) {
                storeTranslation();
            }
            // esc
            else if (e.keyCode === 27) {
                $(this).blur();
            }
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

var HomePage = (function () {
    function toggleStarred(id) {
        Loader.show();

        $.ajax({
            url: '/text/toggle-star',
            method: 'POST',
            data: {id: id}
        }).done(function (msg) {
            if (msg.success === 1) {
                window.location.reload(false);
            }

            Loader.hide();
        });
    }

    function setEvents() {
        $('[data-target="toggle-star"]').on('click', function (e) {
            toggleStarred($(e.currentTarget).attr('data-id'));
        });
    }

    return {
        init: function () {
            $('#text-list').DataTable(
                {
                    order: [[ 0, 'desc' ], [ 3, 'desc' ]],
                    responsive: Util.mobileAndTabletcheck()
                }
            );

            setEvents();
        }
    }
})();

var LanguagePage = (function () {
    return {
        init: function () {
            $('#language-list').DataTable(
                {
                    responsive: Util.mobileAndTabletcheck()
                }
            );
        }
    }
})();
