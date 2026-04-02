<?php if (isLoggedIn() && (strpos($_SERVER['REQUEST_URI'], '/admin') === 0 || strpos($_SERVER['REQUEST_URI'], '/developer') === 0)): ?>
            </main>
        </div>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>

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
