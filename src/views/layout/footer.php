<?php if (isLoggedIn() && (strpos($_SERVER['REQUEST_URI'], '/admin') === 0 || strpos($_SERVER['REQUEST_URI'], '/developer') === 0)): ?>
            </div>
            </main>
        </div>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>

<?php
$systemVersion = function_exists('getSystemSetting') ? (string)getSystemSetting('system_version', '') : '';
$systemVersion = $systemVersion !== '' ? $systemVersion : '1.0.0';
?>
<footer class="text-center text-muted small py-2 border-top bg-white">
    v.<?= htmlspecialchars($systemVersion) ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- jQuery & Mask Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        // Masks
        $('input[name="cpf"]').mask('000.000.000-00');
        $('input[name="zip_code"]').mask('00000-000');
        
        // Dynamic Money Mask
        // Add more masks as needed
        $('.cpf').mask('000.000.000-00');
        $('.cep').mask('00000-000');
        $('.money').mask('#.##0,00', {reverse: true});

        // Máscara de Telefone Inteligente (8 ou 9 dígitos)
        var behavior = function (val) {
            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        },
        options = {
            onKeyPress: function (val, e, field, options) {
                field.mask(behavior.apply({}, arguments), options);
            }
        };
        $('input[name="phone"], input[name="contact_phone"], .phone').mask(behavior, options);

        function normalizeButtonIconSpacing(root) {
            var scope = root ? $(root) : $(document);
            scope.find('a.btn, button.btn, .btn').each(function(){
                var btn = this;
                var icons = btn.querySelectorAll('i, svg.svg-inline--fa');
                if (!icons.length) return;
                icons.forEach(function(icon){
                    var cls = icon.getAttribute('class') || '';
                    var hasMe = /\bme-\d\b|\bme-auto\b/.test(cls);
                    var hasMs = /\bms-\d\b|\bms-auto\b/.test(cls);

                    var hasTextBefore = false;
                    var prev = icon.previousSibling;
                    while (prev) {
                        if (prev.nodeType === 3 && String(prev.textContent || '').trim() !== '') { hasTextBefore = true; break; }
                        if (prev.nodeType === 1 && String(prev.textContent || '').trim() !== '') { hasTextBefore = true; break; }
                        prev = prev.previousSibling;
                    }

                    var hasTextAfter = false;
                    var next = icon.nextSibling;
                    while (next) {
                        if (next.nodeType === 3 && String(next.textContent || '').trim() !== '') { hasTextAfter = true; break; }
                        if (next.nodeType === 1 && String(next.textContent || '').trim() !== '') { hasTextAfter = true; break; }
                        next = next.nextSibling;
                    }

                    if (hasTextAfter && !hasMe) icon.classList.add('me-1');
                    if (hasTextBefore && !hasMs) icon.classList.add('ms-1');
                });
            });
        }

        normalizeButtonIconSpacing();
        (function(){
            var scheduled = 0;
            var obs = new MutationObserver(function(mutations){
                if (scheduled) return;
                scheduled = window.setTimeout(function(){
                    scheduled = 0;
                    normalizeButtonIconSpacing();
                }, 50);
            });
            obs.observe(document.body, { childList: true, subtree: true });
        })();

        if ($.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    decimal: ',',
                    thousands: '.',
                    emptyTable: 'Nenhum dado disponível na tabela',
                    info: 'Mostrando _START_ até _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 até 0 de 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros no total)',
                    infoPostFix: '',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    loadingRecords: 'Carregando...',
                    processing: 'Processando...',
                    search: '',
                    searchPlaceholder: 'Pesquisar...',
                    zeroRecords: 'Nenhum registro encontrado',
                    paginate: {
                        first: 'Primeiro',
                        last: 'Último',
                        next: 'Próximo',
                        previous: 'Anterior'
                    },
                    aria: {
                        sortAscending: ': ativar para ordenar a coluna em ordem crescente',
                        sortDescending: ': ativar para ordenar a coluna em ordem decrescente'
                    }
                }
            });
        }

        $('.dataTables_wrapper .dataTables_filter').each(function(){
            var $filter = $(this);
            var $label = $filter.find('label');
            if ($label.length) {
                $label.contents().filter(function(){
                    return this.nodeType === 3;
                }).remove();
            }
            var $input = $filter.find('input[type="search"]');
            if ($input.length) {
                $input.addClass('w-100');
                if (!$input.attr('placeholder')) {
                    $input.attr('placeholder', 'Pesquisar...');
                }
                if (!$input.attr('aria-label')) {
                    $input.attr('aria-label', 'Pesquisar');
                }
            }
        });

        $('input[type="search"]').each(function(){
            var $input = $(this);
            $input.addClass('w-100');
            if (!$input.attr('placeholder')) {
                $input.attr('placeholder', 'Pesquisar...');
            }
            if ($input.attr('id')) {
                var $lbl = $('label[for="' + $input.attr('id') + '"]');
                if ($lbl.length) $lbl.addClass('visually-hidden');
            }
        });
    });

    // Fechar menu mobile automaticamente ao clicar em um link
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.navbar-collapse .nav-link');
        const menuToggle = document.querySelector('.navbar-toggler');
        const bsCollapse = document.querySelector('.navbar-collapse');

        if (bsCollapse) {
            navLinks.forEach((l) => {
                l.addEventListener('click', () => {
                    if (bsCollapse.classList.contains('show')) {
                        // Bootstrap 5 collapse toggle logic or simply removing show
                        new bootstrap.Collapse(bsCollapse).hide();
                    }
                });
            });
        }
    });
</script>
</body>
</html>
