<?php
// Widget flutuante compartilhado (Doação/Oração/Devocional/Harpa) + modal do
// devocional do dia. Usado por todas as páginas públicas (home embute sua
// própria cópia por já ter sido testada; as demais incluem este parcial).
$siteProfile = $siteProfile ?? getChurchSiteProfileSettings();
$prayerLink = $prayerLink ?? '/oracao';
$prayerTarget = $prayerTarget ?? '_self';
?>
<style>
        @keyframes ctaShimmer {
            0% { transform: rotate(25deg) translateX(-140%); opacity: 0; }
            12% { opacity: 0.35; }
            28% { transform: rotate(25deg) translateX(260%); opacity: 0; }
            100% { transform: rotate(25deg) translateX(260%); opacity: 0; }
        }
        .floating-faith-toggle,
        .floating-faith-action {
            position: relative;
            overflow: hidden;
        }

        .floating-faith-toggle::after,
        .floating-faith-action::after {
            content: "";
            position: absolute;
            top: -30%;
            left: -30%;
            width: 60%;
            height: 160%;
            background: rgba(255,255,255,0.24);
            transform: rotate(25deg) translateX(-140%);
            animation: ctaShimmer 3.2s ease-in-out infinite;
        }
        .floating-faith-actions {
            position: fixed;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1085;
            display: flex;
            flex-direction: column;
            gap: .6rem;
            transition: transform .22s ease;
        }
        .floating-faith-actions.is-suppressed {
            pointer-events: none;
            transform: translateY(-50%) translateX(140%);
        }
        .floating-faith-item {
            display: block;
        }
        .floating-faith-card {
            position: relative;
            z-index: 1;
            width: 46px;
            height: 46px;
        }
        .floating-faith-card.is-expanded {
            z-index: 20;
        }
        .floating-faith-toggle {
            width: 46px;
            height: 46px;
            border: none;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #fff;
            box-shadow: 0 10px 22px rgba(0,0,0,0.22);
            -webkit-tap-highlight-color: transparent;
        }
        .floating-faith-toggle:focus-visible,
        .floating-faith-action:focus-visible {
            outline: 2px solid rgba(255,255,255,0.72);
            outline-offset: 2px;
        }
        .floating-faith-icon {
            font-size: 1.1rem;
            display: inline-flex;
        }
        .floating-faith-popover {
            display: none;
            position: absolute;
            top: 50%;
            right: calc(100% + 10px);
            transform: translateY(-50%);
            width: 224px;
            border-radius: 16px;
            color: #fff;
            padding: .65rem .85rem .85rem;
            box-shadow: 0 16px 30px rgba(0,0,0,0.3);
        }
        .floating-faith-card.is-expanded .floating-faith-popover {
            display: block;
        }
        .floating-faith-content strong {
            display: block;
            font-size: .88rem;
            margin-bottom: .2rem;
        }
        .floating-faith-content span {
            display: block;
            font-size: .76rem;
            opacity: .92;
            line-height: 1.3;
            margin-bottom: .6rem;
        }
        .floating-faith-action {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: .55rem .75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            text-decoration: none;
            color: #fff;
            background: rgba(255,255,255,0.2);
            font-weight: 700;
            font-size: .8rem;
            white-space: nowrap;
            transition: background .16s ease;
        }
        .floating-faith-action:hover {
            color: #fff;
            background: rgba(255,255,255,0.3);
        }
        @media (hover: hover) and (pointer: fine) {
            .floating-faith-card:not(.is-suppressed):hover {
                z-index: 20;
            }
            .floating-faith-card:not(.is-suppressed):hover .floating-faith-popover {
                display: block;
            }
        }
        .floating-faith-toggle, .floating-faith-popover {
            background: var(--faith-gradient);
        }
        .floating-faith-card-donate {
            --faith-gradient: linear-gradient(135deg, #ff8a00 0%, #b30000 100%);
        }
        .floating-faith-card-prayer {
            --faith-gradient: linear-gradient(135deg, #8b1538 0%, #c62662 100%);
        }
        .floating-faith-card-devotional {
            --faith-gradient: linear-gradient(135deg, #3c1d25 0%, #6f3a54 100%);
        }
        .floating-faith-card-harpa {
            --faith-gradient: linear-gradient(135deg, #ff2a7a 0%, #d4af37 100%);
        }
        .devotional-modal .modal-content {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 28px 60px rgba(0,0,0,0.2);
        }
        .devotional-modal-header {
            padding: 1.2rem 1.35rem 1rem;
            background: linear-gradient(135deg, rgba(255,42,122,0.1), rgba(179,0,0,0.08));
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .devotional-modal-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: #3c1d25;
        }
        .devotional-modal-subtitle {
            margin: .35rem 0 0;
            color: #6b7280;
            font-size: .93rem;
        }
        .devotional-modal-body {
            padding: 1.35rem;
            background: #fffdfd;
        }
        .devotional-verse-card {
            position: relative;
            border-radius: 20px;
            background: linear-gradient(180deg, #fff, #fff5f8);
            border: 1px solid rgba(179,0,0,0.08);
            padding: 1.2rem;
        }
        .devotional-verse-share {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #8b1538;
            background: rgba(255,255,255,0.88);
            box-shadow: 0 10px 20px rgba(60,29,37,0.08);
            transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
        }
        .devotional-verse-share:hover {
            color: #8b1538;
            background: #fff;
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(60,29,37,0.12);
        }
        .devotional-verse-share:focus-visible {
            outline: 2px solid rgba(139,21,56,0.32);
            outline-offset: 2px;
        }
        .devotional-verse-label {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .38rem .72rem;
            border-radius: 999px;
            background: rgba(255,42,122,0.1);
            color: #8b1538;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .devotional-verse-meta {
            margin-top: .8rem;
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }
        .devotional-verse-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            padding: .34rem .65rem;
            border-radius: 999px;
            background: rgba(60,29,37,0.06);
            color: #5b3341;
            font-size: .76rem;
            font-weight: 700;
        }
        .devotional-verse-text {
            margin: 1rem 0 .8rem;
            font-size: 1.22rem;
            line-height: 1.75;
            color: #2d1a21;
            font-weight: 600;
        }
        .devotional-verse-reference {
            color: #8b1538;
            font-weight: 800;
            margin-bottom: 0;
        }
        .devotional-modal-actions {
            margin-top: 1rem;
            display: grid;
            gap: .95rem;
        }
        .devotional-modal-note {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem .95rem;
            border-radius: 16px;
            background: linear-gradient(180deg, #fffefe, #fff8fa);
            border: 1px solid rgba(139,21,56,0.06);
        }
        .devotional-modal-note-icon {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 30px;
            color: #8b1538;
            background: rgba(255,42,122,0.08);
            border: 1px solid rgba(139,21,56,0.08);
            font-size: .82rem;
        }
        .devotional-modal-note strong {
            display: block;
            color: #3c1d25;
            font-size: .9rem;
            line-height: 1.2;
        }
        .devotional-modal-note span {
            display: block;
            margin-top: .18rem;
            color: #6b7280;
            font-size: .84rem;
            line-height: 1.45;
        }
        .devotional-modal-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        .devotional-modal-button {
            min-height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .8rem 1rem;
        }
        .devotional-share-feedback {
            min-height: 1.2rem;
            margin: -.15rem .2rem 0;
            color: #8b1538;
            font-size: .82rem;
            font-weight: 600;
            text-align: center;
        }
    @media (max-width: 767.98px) {
        .floating-faith-actions {
            right: 10px;
            top: auto;
            bottom: 90px;
            transform: none;
            gap: .5rem;
        }
        .floating-faith-actions.is-suppressed {
            transform: translateX(120%);
        }
        .floating-faith-card {
            width: 42px;
            height: 42px;
        }
        .floating-faith-toggle {
            width: 42px;
            height: 42px;
        }
        .floating-faith-popover {
            width: 196px;
        }
        .floating-faith-content strong {
            font-size: .82rem;
        }
        .floating-faith-content span {
            font-size: .7rem;
        }
        .floating-faith-action {
            padding: .5rem .7rem;
            font-size: .78rem;
        }
        .devotional-modal-header,
        .devotional-modal-body {
            padding: 1rem;
        }
        .devotional-verse-text {
            font-size: 1.02rem;
            line-height: 1.65;
        }
        .devotional-verse-share {
            top: .9rem;
            right: .9rem;
            width: 38px;
            height: 38px;
        }
    }
</style>

    <div class="floating-faith-actions" aria-label="Ações rápidas">
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-donate is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir doação">
                    <span class="floating-faith-icon"><i class="fas fa-hand-holding-heart"></i></span>
                </button>
                <div class="floating-faith-popover">
                    <div class="floating-faith-content">
                        <strong>Doação</strong>
                        <span>Contribua com a obra via PIX.</span>
                    </div>
                    <a href="/doacao" class="floating-faith-action">
                        <i class="fas fa-qrcode"></i> Fazer doação
                    </a>
                </div>
            </div>
        </div>
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-prayer is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir oração">
                    <span class="floating-faith-icon"><i class="fas fa-hands-praying"></i></span>
                </button>
                <div class="floating-faith-popover">
                    <div class="floating-faith-content">
                        <strong>Oração</strong>
                        <span>Fale com a igreja e compartilhe seu pedido.</span>
                    </div>
                    <a href="<?= htmlspecialchars($prayerLink) ?>" class="floating-faith-action" target="<?= htmlspecialchars($prayerTarget) ?>"<?= $prayerTarget === '_blank' ? ' rel="noopener noreferrer"' : '' ?>>
                        <i class="fas fa-paper-plane"></i> Fazer pedido
                    </a>
                </div>
            </div>
        </div>
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-devotional is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir devocional">
                    <span class="floating-faith-icon"><i class="fas fa-book-bible"></i></span>
                </button>
                <div class="floating-faith-popover">
                    <div class="floating-faith-content">
                        <strong>Devocional</strong>
                        <span>Abra um devocional e receba uma meditação.</span>
                    </div>
                    <button type="button" class="floating-faith-action" data-bs-toggle="modal" data-bs-target="#devotionalModal">
                        <i class="fas fa-sparkles"></i> Abrir palavra
                    </button>
                </div>
            </div>
        </div>
        <div class="floating-faith-item">
            <div class="floating-faith-card floating-faith-card-harpa is-collapsed" data-faith-card>
                <button type="button" class="floating-faith-toggle" data-faith-toggle aria-expanded="false" aria-label="Abrir Harpa Cristã">
                    <span class="floating-faith-icon"><i class="fas fa-music"></i></span>
                </button>
                <div class="floating-faith-popover">
                    <div class="floating-faith-content">
                        <strong>Harpa Cristã</strong>
                        <span>Consulte hinos e letras.</span>
                    </div>
                    <a href="/harpa" class="floating-faith-action">
                        <i class="fas fa-book-open"></i> Abrir Harpa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade devotional-modal" id="devotionalModal" tabindex="-1" aria-labelledby="devotionalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="devotional-modal-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="devotional-modal-title" id="devotionalModalLabel">Devocional do Dia</h2>
                            <p class="devotional-modal-subtitle">Abra quando quiser uma palavra de encorajamento, meditação e fé.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                </div>
                <div class="devotional-modal-body">
                    <div class="devotional-verse-card">
                        <button type="button" class="devotional-verse-share" id="devotionalShareButton" aria-label="Compartilhar mensagem">
                            <i class="fas fa-share-nodes"></i>
                        </button>
                        <span class="devotional-verse-label"><i class="fas fa-sparkles"></i> Palavra para o seu coração</span>
                        <div class="devotional-verse-meta">
                            <span class="devotional-verse-chip" id="devotionalVerseTheme"><i class="fas fa-bookmark"></i> Tema</span>
                            <span class="devotional-verse-chip" id="devotionalVerseTestament"><i class="fas fa-scroll"></i> Testamento</span>
                        </div>
                        <blockquote class="devotional-verse-text mb-0" id="devotionalVerseText"></blockquote>
                        <p class="devotional-verse-reference" id="devotionalVerseReference"></p>
                    </div>
                    <div class="devotional-modal-actions">
                        <div class="devotional-modal-note">
                            <span class="devotional-modal-note-icon"><i class="fas fa-heart"></i></span>
                            <div>
                                <strong>Momento de meditar</strong>
                                <span>Leia com calma, guarde a palavra no coracao e compartilhe essa mensagem se ela falou com voce.</span>
                            </div>
                        </div>
                        <div class="devotional-modal-buttons">
                            <button type="button" class="btn btn-primary devotional-modal-button" id="devotionalShuffleButton"><i class="fas fa-shuffle"></i> Nova palavra</button>
                        </div>
                        <div class="devotional-share-feedback" id="devotionalShareFeedback" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
            const faithCards = document.querySelectorAll('[data-faith-card]');
            const faithToggles = document.querySelectorAll('[data-faith-toggle]');
            const faithDock = document.querySelector('.floating-faith-actions');
            const homeModals = document.querySelectorAll('.modal');
            const devotionalPool = <?= json_encode(getDevotionalVerses(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const devotionalModal = document.getElementById('devotionalModal');
            const devotionalVerseText = document.getElementById('devotionalVerseText');
            const devotionalVerseReference = document.getElementById('devotionalVerseReference');
            const devotionalVerseTheme = document.getElementById('devotionalVerseTheme');
            const devotionalVerseTestament = document.getElementById('devotionalVerseTestament');
            const devotionalShuffleButton = document.getElementById('devotionalShuffleButton');
            const devotionalShareButton = document.getElementById('devotionalShareButton');
            const devotionalShareFeedback = document.getElementById('devotionalShareFeedback');
            let devotionalShareFeedbackTimer = 0;

            function setDevotionalShareFeedback(message) {
                if (!devotionalShareFeedback) return;
                devotionalShareFeedback.textContent = message || '';
                if (devotionalShareFeedbackTimer) {
                    window.clearTimeout(devotionalShareFeedbackTimer);
                }
                if (message) {
                    devotionalShareFeedbackTimer = window.setTimeout(function() {
                        devotionalShareFeedback.textContent = '';
                    }, 2800);
                }
            }

            function getCurrentDevotionalShareText() {
                const verseText = devotionalVerseText ? devotionalVerseText.textContent.trim() : '';
                const verseReference = devotionalVerseReference ? devotionalVerseReference.textContent.trim() : '';
                const verseTheme = devotionalVerseTheme ? devotionalVerseTheme.textContent.replace('Tema:', '').trim() : '';
                const churchLabel = <?= json_encode($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                if (!verseText && !verseReference) return 'Palavra para o seu coração';
                return verseText + (verseReference ? ' - ' + verseReference : '') + (verseTheme ? ' | Tema: ' + verseTheme : '') + (churchLabel ? ' | ' + churchLabel : '');
            }

            function setFaithCardState(card, expanded) {
                if (!card) return;
                card.classList.toggle('is-expanded', expanded);
                card.classList.toggle('is-collapsed', !expanded);
                const toggle = card.querySelector('[data-faith-toggle]');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                }
            }

            faithToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    const card = toggle.closest('[data-faith-card]');
                    const shouldExpand = card ? !card.classList.contains('is-expanded') : false;

                    faithCards.forEach(function(item) {
                        setFaithCardState(item, item === card ? shouldExpand : false);
                    });
                });
            });

            document.addEventListener('click', function(event) {
                if (event.target.closest('.floating-faith-actions')) return;
                faithCards.forEach(function(card) {
                    setFaithCardState(card, false);
                });
            });

            homeModals.forEach(function(modalEl) {
                modalEl.addEventListener('show.bs.modal', function() {
                    faithCards.forEach(function(card) {
                        setFaithCardState(card, false);
                    });
                    if (faithDock) {
                        faithDock.classList.add('is-suppressed');
                    }
                });

                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (faithDock) {
                        faithDock.classList.remove('is-suppressed');
                    }
                });
            });

            function renderDevotional(index) {
                if (!Array.isArray(devotionalPool) || !devotionalPool.length || !devotionalVerseText || !devotionalVerseReference) return;
                const safeIndex = Math.max(0, index % devotionalPool.length);
                const verse = devotionalPool[safeIndex];
                devotionalVerseText.textContent = '"' + (verse.text || '') + '"';
                devotionalVerseReference.textContent = verse.reference || '';
                if (devotionalVerseTheme) {
                    devotionalVerseTheme.innerHTML = '<i class="fas fa-bookmark"></i> Tema: ' + (verse.theme || 'Geral');
                }
                if (devotionalVerseTestament) {
                    devotionalVerseTestament.innerHTML = '<i class="fas fa-scroll"></i> ' + (verse.testament || 'Testamento');
                }
                setDevotionalShareFeedback('');
            }

            if (Array.isArray(devotionalPool) && devotionalPool.length) {
                const today = new Date();
                const seed = (today.getDate() + today.getMonth() + today.getFullYear()) % devotionalPool.length;
                renderDevotional(seed);

                if (devotionalShuffleButton) {
                    devotionalShuffleButton.addEventListener('click', function() {
                        const randomIndex = Math.floor(Math.random() * devotionalPool.length);
                        renderDevotional(randomIndex);
                    });
                }

                if (devotionalShareButton) {
                    devotionalShareButton.addEventListener('click', async function() {
                        const shareText = getCurrentDevotionalShareText();
                        const sharePayload = {
                            title: 'Devocional do Dia - ' + <?= json_encode($siteProfile['alias'] ?? $siteProfile['name'] ?? 'Igreja', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                            text: shareText
                        };

                        try {
                            if (navigator.share) {
                                await navigator.share(sharePayload);
                                setDevotionalShareFeedback('Mensagem compartilhada com sucesso.');
                                return;
                            }

                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                await navigator.clipboard.writeText(shareText);
                                setDevotionalShareFeedback('Mensagem copiada para compartilhar.');
                                return;
                            }

                            const tempField = document.createElement('textarea');
                            tempField.value = shareText;
                            tempField.setAttribute('readonly', 'readonly');
                            tempField.style.position = 'absolute';
                            tempField.style.left = '-9999px';
                            document.body.appendChild(tempField);
                            tempField.select();
                            document.execCommand('copy');
                            document.body.removeChild(tempField);
                            setDevotionalShareFeedback('Mensagem copiada para compartilhar.');
                        } catch (error) {
                            setDevotionalShareFeedback('Nao foi possivel compartilhar agora.');
                        }
                    });
                }
            }

            function openDevotionalModal() {
                if (!devotionalModal || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
                bootstrap.Modal.getOrCreateInstance(devotionalModal).show();
            }

            try {
                const searchParams = new URLSearchParams(window.location.search || '');
                if (searchParams.get('devocional') === '1' || window.location.hash === '#devocional' || window.location.pathname === '/devocional') {
                    openDevotionalModal();
                }
            } catch (error) {}
});
</script>
