$(function () {
    'use strict';

    // Mengatur placeholder awal pada elemen select2 dalam form-floating
    $('.form-floating > .form-select.select2, .form-floating > .custom-select.select2').each(function () {
        $(this).attr('data-placeholder', this.value === "" ? 'true' : 'false');
    });

    // Update placeholder saat select2 mendapat fokus atau user memilih/clear pilihan
    $('.form-floating > .form-select.select2, .form-floating > .custom-select.select2')
        .on('select2:open', function () {
            $(this).attr('data-placeholder', 'false');
        })
        .on('select2:close select2:select select2:unselect', function () {
            $(this).attr('data-placeholder', this.value === "" ? 'true' : 'false');
        });

    // Tambahan: Update class pada select2 multiple agar input search muncul saat focus
    $(document).on('select2:open', function (e) {
        const $select = $(e.target);
        if ($select.prop('multiple')) {
            const $container = $select.next('.select2-container');
            $container.find('.select2-selection--multiple').addClass('focus-visible');
        }
    });

    // Tambahan: Hilangkan search field saat tidak focus
    $(document).on('select2:close', function (e) {
        const $select = $(e.target);
        if ($select.prop('multiple')) {
            const $container = $select.next('.select2-container');
            $container.find('.select2-selection--multiple').removeClass('focus-visible');
        }
    });

    // Tambahan: Update label naik/turun saat pilihan berubah
    $('.form-floating .select2').on('change', function () {
        const $select = $(this);
        const hasValue = $select.val() && $select.val().length > 0;
        $select.attr('data-placeholder', hasValue ? 'false' : 'true');
    });

    // Mengelola kondisi input date dan membuka date picker pada klik
    document.querySelectorAll('.form-floating > .form-control[type="date"], .form-floating > .form-control[type="time"], .form-floating > .form-control[type="datetime-local"]').forEach(input => {
        const updateClasses = () => {
            if (input.value) {
                input.classList.add('value-present');
                input.classList.remove('placeholder-active');
            } else {
                input.classList.remove('value-present');
                input.classList.add('placeholder-active');
            }
        };

        updateClasses();
        input.addEventListener('input', updateClasses);
        input.addEventListener('focus', updateClasses);
        input.addEventListener('blur', updateClasses);
        input.addEventListener('click', () => input.showPicker());
    });

    // Mengelola kondisi select
    document.querySelectorAll('.form-floating .form-select, .form-floating .custom-select').forEach(select => {
        select.setAttribute('data-placeholder', select.value === "" ? 'true' : 'false');
        select.addEventListener('change', function () {
            this.setAttribute('data-placeholder', this.value === "" ? 'true' : 'false');
        });
    });

    // Membuka dan menutup off-canvas
    $('.off-canvas-menu').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $(target).addClass('show');
    });

    $('.off-canvas .close').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.off-canvas').removeClass('show');
    });

    $(document).on('click touchstart', function (e) {
        e.stopPropagation();
        if (!$(e.target).closest('.off-canvas-menu').length && !$(e.target).closest('.off-canvas').length) {
            $('.off-canvas.show').removeClass('show');
        }
    });

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Dropdown menu tetap terbuka ketika diklik
    $(document).on('click mousedown touchstart', '.allow-focus', function (e) {
        e.stopPropagation();
    });

    // Menampilkan nama file di input file floating (mendukung multiple)
    $(document).on("change", ".custom-file-input", function () {
        var $input = $(this);
        var $label = $input.siblings("label");
        // Pastikan ini varian "floating" (label berisi .custom-file-name)
        var $fileNameElement = $label.find(".custom-file-name");
        var $labelText = $label.find(".custom-file-label-text");
        if (!$fileNameElement.length) return; // bukan floating, biarkan handler lain yang urus

        var names = [];
        var files = this.files;

        if (files && files.length) {
            for (var i = 0; i < files.length; i++) {
                names.push(files[i].name);
            }
        } else {
            // fallback untuk browser lama
            var v = $input.val();
            if (v) names.push(v.split("\\").pop());
        }

        var joined = names.join(", ");
        if (joined) {
            $fileNameElement.html(joined);
            $labelText.addClass("active");
        } else {
            // reset jika batal pilih file
            $fileNameElement.html("");
            $labelText.removeClass("active");
        }
    });

    // Menampilkan nama file pada label input file bawaan (mendukung multiple)
    $(document).on("change", ".custom-file-input", function () {
        var $input = $(this);
        var $label = $input.next(".custom-file-label");
        if (!$label.length) return;

        // Jika label ini milik varian floating (punya .custom-file-label-text), abaikan di handler ini
        if ($label.find(".custom-file-label-text").length) return;

        // Simpan teks awal label sekali, agar bisa dikembalikan saat dibatalkan
        if (!$label.data("defaultText")) {
            $label.data("defaultText", $label.html());
        }

        var names = [];
        var files = this.files;

        if (files && files.length) {
            for (var i = 0; i < files.length; i++) {
                names.push(files[i].name);
            }
        } else {
            // fallback untuk browser lama
            var v = $input.val();
            if (v) names.push(v.split("\\").pop());
        }

        if (names.length) {
            $label.addClass("selected").html(names.join(", "));
        } else {
            // reset jika batal pilih file
            $label.removeClass("selected").html($label.data("defaultText"));
        }
    });

    // Kembali ke halaman sebelumnya
    $('#btn-back').on('click', function () {
        history.back();
    });

    // Select2 Floating
    $(function () {
        'use strict';

        $('.select2').each(function () {
            const $select = $(this);
            const $container = $select.next('.select2-container');

            if ($select.prop('multiple')) {
                if (!$select.val() || $select.val().length === 0) {
                    $container.addClass('select2-placeholder-transparent');
                }
            } else {
                if (!$select.val()) {
                    $container.addClass('select2-placeholder-transparent');
                }
            }

            if ($select.prop('disabled')) {
                $container.addClass('select2-disabled');
            }
        })
            .on('select2:open', function () {
                const $container = $(this).next('.select2-container');
                $container.removeClass('select2-placeholder-transparent');
            })
            .on('select2:close', function () {
                const $select = $(this);
                const $container = $select.next('.select2-container');

                if ($select.prop('multiple')) {
                    if (!$select.val() || $select.val().length === 0) {
                        $container.addClass('select2-placeholder-transparent');
                    }
                } else {
                    if (!$select.val()) {
                        $container.addClass('select2-placeholder-transparent');
                    }
                }
            })
            .on('change', function () {
                const $select = $(this);
                const $container = $select.next('.select2-container');

                if ($select.prop('multiple')) {
                    if (!$select.val() || $select.val().length === 0) {
                        $container.addClass('select2-placeholder-transparent');
                    } else {
                        $container.removeClass('select2-placeholder-transparent');
                    }
                } else {
                    if (!$select.val()) {
                        $container.addClass('select2-placeholder-transparent');
                    } else {
                        $container.removeClass('select2-placeholder-transparent');
                    }
                }
            })
            .on('select2:unselecting', function (e) {
                const $select = $(this);
                if ($select.prop('multiple') && $select.val().length === 1) {
                    $select.next('.select2-container').addClass('select2-placeholder-transparent');
                }
            })
            .on('select2:clear', function () {
                $(this).next('.select2-container').addClass('select2-placeholder-transparent');
            });
    });
});

// Konfigurasi expand
document.querySelectorAll('[data-toggle="expand"]').forEach(button => {
    button.addEventListener('click', function (event) {
        event.preventDefault();

        const targetId = this.getAttribute('data-target') || this.getAttribute('href').replace('#', '');
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
            const isExpanded = this.getAttribute('aria-expanded') === "true";

            if (!targetElement.dataset.maxHeight) {
                targetElement.dataset.maxHeight = targetElement.style.maxHeight || "40px";
            }

            if (isExpanded) {
                targetElement.style.maxHeight = targetElement.dataset.maxHeight;
                targetElement.classList.remove('show');
            } else {
                targetElement.style.maxHeight = targetElement.scrollHeight + "px";
                targetElement.classList.add('show');
            }

            this.setAttribute('aria-expanded', !isExpanded);
        }
    });
});

// Responsif breakpoint expand
window.addEventListener("resize", () => {
    document.querySelectorAll(".expand.show").forEach(expand => {
        expand.style.maxHeight = expand.scrollHeight + "px";
    });
});

// Navbar scroll effect
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar-header-scroll');

    function handleScroll() {
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    handleScroll();
    window.addEventListener('scroll', handleScroll);
});

// Input Tags expand hint
$(document).on('typeahead:open typeahead:render', '.bootstrap-tagsinput .tt-input', function () {
    var $typeaheadInput = $(this);
    var $tagsContainer = $typeaheadInput.closest('.bootstrap-tagsinput');
    var $typeaheadMenu = $tagsContainer.find('.tt-menu');

    if (!$tagsContainer.length || !$typeaheadMenu.length) return;

    function alignMenu() {
        var containerRect = $tagsContainer[0].getBoundingClientRect();
        var wrapperRect = $typeaheadInput.closest('.twitter-typeahead')[0].getBoundingClientRect();

        $typeaheadMenu.css({
            left: containerRect.left - wrapperRect.left,
            top: containerRect.bottom - wrapperRect.top,
            width: containerRect.width
        });
    }

    alignMenu();

    $(window).off('resize.typeaheadAlign').on('resize.typeaheadAlign', alignMenu);
});