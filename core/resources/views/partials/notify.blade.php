<link href="{{ asset('assets/global/css/iziToast.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/global/css/iziToast_custom.css') }}" rel="stylesheet">
<script src="{{ asset('assets/global/js/iziToast.min.js') }}"></script>

<script>
    "use strict";

    // --- VINANCE INSTITUTIONAL WEB AUDIO SOUND SYNTHESIZER ---
    window.vinanceAudioCtx = null;
    window.isVinanceSoundEnabled = function() {
        var setting = localStorage.getItem('vinance_sound_enabled');
        return setting === null || setting === 'true' || setting === true;
    };

    window.toggleVinanceSound = function() {
        var current = window.isVinanceSoundEnabled();
        var next = !current;
        localStorage.setItem('vinance_sound_enabled', next);
        window.updateSoundToggleUI();
        if (next) {
            window.playVinanceSound('ping');
        }
        return next;
    };

    window.updateSoundToggleUI = function() {
        var enabled = window.isVinanceSoundEnabled();
        var btns = document.querySelectorAll('.vinance-sound-toggle-btn');
        btns.forEach(function(btn) {
            if (enabled) {
                btn.innerHTML = '<i class="las la-volume-up text--success"></i>';
                btn.setAttribute('title', 'Sound Effects: Enabled (Click to Mute)');
                btn.classList.remove('opacity-50');
            } else {
                btn.innerHTML = '<i class="las la-volume-mute text-muted"></i>';
                btn.setAttribute('title', 'Sound Effects: Muted (Click to Unmute)');
                btn.classList.add('opacity-50');
            }
        });
    };

    window.playVinanceSound = function(type) {
        if (!window.isVinanceSoundEnabled()) return;

        try {
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            if (!window.vinanceAudioCtx) {
                window.vinanceAudioCtx = new AudioContext();
            }

            if (window.vinanceAudioCtx.state === 'suspended') {
                window.vinanceAudioCtx.resume();
            }

            var ctx = window.vinanceAudioCtx;
            var now = ctx.currentTime;

            if (type === 'success' || type === 'order') {
                // Dual chord shimmer (880Hz & 1320Hz)
                var osc1 = ctx.createOscillator();
                var osc2 = ctx.createOscillator();
                var gain = ctx.createGain();

                osc1.type = 'sine';
                osc2.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                osc2.frequency.setValueAtTime(1320, now + 0.08);

                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.45);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now + 0.08);
                osc1.stop(now + 0.45);
                osc2.stop(now + 0.45);

            } else if (type === 'swap') {
                // Futuristic frequency sweep
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(520, now);
                osc.frequency.exponentialRampToValueAtTime(1040, now + 0.15);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.35);

                gain.gain.setValueAtTime(0.09, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.4);

            } else if (type === 'harvest') {
                // Sparkling 4-note ascending arpeggio
                var freqs = [784, 1046, 1318, 1568];
                freqs.forEach(function(f, idx) {
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    var noteTime = now + (idx * 0.06);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(f, noteTime);

                    gain.gain.setValueAtTime(0.07, noteTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, noteTime + 0.25);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(noteTime);
                    osc.stop(noteTime + 0.25);
                });

            } else if (type === 'bot') {
                // Institutional neural tone
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.2);

                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.4);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.4);

            } else {
                // Subtle ping
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(980, now);

                gain.gain.setValueAtTime(0.06, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.2);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.2);
            }
        } catch (e) {
            console.warn('Audio synthesis muted or unavailable:', e);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.updateSoundToggleUI();
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.vinance-sound-toggle-btn')) {
                e.preventDefault();
                window.toggleVinanceSound();
            }
        });
    });

    const colors = {
        success: '#28c76f',
        error: '#eb2222',
        warning: '#ff9f43',
        info: '#1e9ff2',
    }

    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-exclamation-circle',
    }

    const notifications = @json(session('notify', []));
    const errors = @json(@$errors ? collect($errors->all())->unique() : []);

    const triggerToaster = (status, message) => {
        if (status === 'success') {
            window.playVinanceSound('success');
        } else {
            window.playVinanceSound('ping');
        }

        iziToast[status]({
            title: status.charAt(0).toUpperCase() + status.slice(1),
            message: message,
            position: "topRight",
            backgroundColor: '#fff',
            icon: icons[status],
            iconColor: colors[status],
            progressBarColor: colors[status],
            titleSize: '1rem',
            messageSize: '1rem',
            titleColor: '#474747',
            messageColor: '#a2a2a2',
            transitionIn: 'bounceInLeft'
        });
    }

    if (notifications.length) {
        notifications.forEach(element => {
            triggerToaster(element[0], element[1]);
        });
    }

    if (errors.length) {
        errors.forEach(error => {
            triggerToaster('error', error);
        });
    }

    function notify(status, message) {
        if (typeof message == 'string') {
            triggerToaster(status, message);
        } else {
            $.each(message, (i, val) => triggerToaster(status, val));
        }
    }
</script>
