/**
 * Doctor Directory - Admin JS
 * Handles form validation, AJAX delete, and live search.
 */

( function( $ ) {
    'use strict';

    // ── Form validation (add/edit) ──────────────────────────────────────────
    var $form = $( '#dd-doctor-form' );

    if ( $form.length ) {
        $form.validate({
            rules: {
                full_name: { required: true, minlength: 3, maxlength: 150 },
                email:     { required: true, email: true },
                address:   { required: true, minlength: 5 },
            },
            messages: {
                full_name: {
                    required:  DD.i18n.fieldRequired,
                    minlength: 'Must be at least 3 characters.',
                    maxlength: 'Must not exceed 150 characters.',
                },
                email: {
                    required: DD.i18n.fieldRequired,
                    email:    DD.i18n.emailInvalid,
                },
                address: {
                    required:  DD.i18n.fieldRequired,
                    minlength: 'Please enter a complete address.',
                },
            },
            // Coloca el error en el span existente en lugar de crear uno nuevo
            errorPlacement: function( error, element ) {
                $( '#' + element.attr( 'id' ) + '-error' ).text( error.text() );
            },
            highlight: function( element ) {
                $( element ).addClass( 'dd-input-error' );
            },
            unhighlight: function( element ) {
                $( element ).removeClass( 'dd-input-error' );
            },
            submitHandler: function( form ) {
                $( '#submit', form ).prop( 'disabled', true ).val( 'Saving…' );
                $( '.dd-submit-feedback' ).addClass( 'is-loading' ).text( 'Saving…' );
                form.submit();
            },
        });

        // Contador de caracteres para el campo nombre
        var $nameInput   = $( '#full_name' );
        var $nameCounter = $( '<span class="dd-char-counter"></span>' );
        $nameInput.after( $nameCounter );

        $nameInput.on( 'input', function() {
            var len = $( this ).val().length;
            $nameCounter.text( len + ' / 150' ).toggleClass( 'dd-counter-warn', len > 130 );
        }).trigger( 'input' );
    }


    // ── AJAX Delete modal ───────────────────────────────────────────────────
    var $modal        = $( '#dd-delete-modal' );
    var $modalName    = $( '#dd-modal-name' );
    var $modalConfirm = $( '#dd-modal-confirm' );
    var $modalCancel  = $( '#dd-modal-cancel' );
    var $modalFeedback = $( '#dd-modal-feedback' );
    var targetId      = null;
    var $targetRow    = null;

    if ( $modal.length ) {

        $( document ).on( 'click', '.dd-action-delete', function() {
            var $btn = $( this );
            targetId    = $btn.data( 'id' );
            $targetRow  = $btn.closest( 'tr' );
            $modalName.text( $btn.data( 'name' ) );
            $modalFeedback.text( '' );
            $modalConfirm.prop( 'disabled', false ).text( 'Delete' );
            $modal.removeAttr( 'hidden' );
            $modalConfirm.focus();
        });

        function closeModal() {
            $modal.attr( 'hidden', '' );
            if ( $targetRow ) $targetRow.removeClass( 'is-deleting' );
            targetId = null;
            $targetRow = null;
        }

        $modalCancel.on( 'click', closeModal );
        $( '.dd-modal-backdrop' ).on( 'click', closeModal );

        $( document ).on( 'keydown', function( e ) {
            if ( e.key === 'Escape' && ! $modal.attr( 'hidden' ) ) closeModal();
        });

        // Focus trap básico dentro del modal
        $modal.on( 'keydown', function( e ) {
            if ( e.key !== 'Tab' ) return;
            var $btns = $modal.find( 'button:not([disabled])' );
            var first = $btns.first()[0];
            var last  = $btns.last()[0];
            if ( e.shiftKey && document.activeElement === first ) {
                e.preventDefault();
                last.focus();
            } else if ( ! e.shiftKey && document.activeElement === last ) {
                e.preventDefault();
                first.focus();
            }
        });

        $modalConfirm.on( 'click', function() {
            if ( ! targetId ) return;

            $modalConfirm.prop( 'disabled', true ).text( DD.i18n.deleting );
            if ( $targetRow ) $targetRow.addClass( 'is-deleting' );

            $.post( DD.ajaxUrl, { action: 'dd_delete_doctor', nonce: DD.nonce, id: targetId })
                .done( function( res ) {
                    if ( res.success ) {
                        var $row = $targetRow;
                        closeModal();
                        if ( $row ) {
                            $row.addClass( 'dd-row-removing' );
                            setTimeout( function() {
                                $row.remove();
                                updateStats();
                                showNotice( 'success', DD.i18n.deleted );
                            }, 350 );
                        }
                    } else {
                        $modalFeedback.text( res.data && res.data.message ? res.data.message : DD.i18n.error );
                        $modalConfirm.prop( 'disabled', false ).text( 'Delete' );
                        if ( $targetRow ) $targetRow.removeClass( 'is-deleting' );
                    }
                })
                .fail( function() {
                    $modalFeedback.text( DD.i18n.error );
                    $modalConfirm.prop( 'disabled', false ).text( 'Delete' );
                    if ( $targetRow ) $targetRow.removeClass( 'is-deleting' );
                });
        });
    }


    // ── Live search (filtrado client-side) ──────────────────────────────────
    var $searchInput = $( '#dd-search-input' );
    var $tbody       = $( '#dd-doctors-tbody' );
    var $statsBar    = $( '.dd-stats-bar' );

    if ( $searchInput.length && $tbody.length ) {

        $searchInput.on( 'input', debounce( function() {
            filterRows( $.trim( $( this ).val() ).toLowerCase() );
        }, 220 ));

        $searchInput.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' ) $( '#dd-search-form' ).submit();
        });
    }

    function filterRows( term ) {
        var $rows   = $tbody.find( '.dd-doctor-row' );
        var visible = 0;

        $tbody.find( '.dd-live-no-results' ).remove();

        $rows.each( function() {
            var $row = $( this );
            if ( ! term || $row.text().toLowerCase().indexOf( term ) !== -1 ) {
                $row.show();
                visible++;
                term ? applyHighlight( $row, term ) : clearHighlight( $row );
            } else {
                $row.hide();
                clearHighlight( $row );
            }
        });

        if ( visible === 0 && term ) {
            $tbody.append(
                '<tr class="dd-no-results dd-live-no-results"><td colspan="5">No results for <strong>"' +
                escHtml( term ) + '"</strong>. <a href="#" class="dd-clear-live">Clear</a></td></tr>'
            );
        }

        updateStats( visible, term );
    }

    $( document ).on( 'click', '.dd-clear-live', function( e ) {
        e.preventDefault();
        $searchInput.val( '' ).trigger( 'input' ).focus();
    });

    function applyHighlight( $row, term ) {
        clearHighlight( $row );
        $row.find( '.column-name a, .column-email a, .column-address' ).each( function() {
            var $el  = $( this );
            var text = $el.text();
            var re   = new RegExp( '(' + escRegex( term ) + ')', 'gi' );
            if ( re.test( text ) ) {
                $el.html( escHtml( text ).replace(
                    new RegExp( '(' + escRegex( escHtml( term ) ) + ')', 'gi' ),
                    '<span class="dd-highlight">$1</span>'
                ));
            }
        });
    }

    function clearHighlight( $row ) {
        $row.find( '.dd-highlight' ).each( function() {
            $( this ).replaceWith( document.createTextNode( $( this ).text() ) );
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    function updateStats( visible, term ) {
        if ( ! $statsBar.length ) return;
        var total = $tbody.find( '.dd-doctor-row' ).length;
        if ( term ) {
            var v = visible !== undefined ? visible : $tbody.find( '.dd-doctor-row:visible' ).length;
            $statsBar.html( 'Showing <strong>' + v + '</strong> result(s) for <em>"' + escHtml( term ) + '"</em> &mdash; ' + total + ' total' );
        } else {
            $statsBar.html( '<strong>' + total + '</strong> doctor(s) registered' );
        }
    }

    function showNotice( type, message ) {
        $( '.dd-dynamic-notice' ).remove();
        var $n = $( '<div class="notice notice-' + type + ' is-dismissible dd-dynamic-notice"><p>' + escHtml( message ) + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button></div>' );
        $( '.wp-header-end' ).after( $n );
        $n.find( '.notice-dismiss' ).on( 'click', function() { $n.remove(); });
        setTimeout( function() { $n.fadeOut( 400, function() { $( this ).remove(); }); }, 4000 );
    }

    function debounce( fn, delay ) {
        var timer;
        return function() {
            var ctx = this, args = arguments;
            clearTimeout( timer );
            timer = setTimeout( function() { fn.apply( ctx, args ); }, delay );
        };
    }

    function escHtml( str ) {
        return String( str ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
    }

    function escRegex( str ) {
        return String( str ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
    }

    // ── Toggle status (Activate / Deactivate) ───────────────────────────────
    $( document ).on( 'click', '.dd-action-toggle', function() {
        var $btn   = $( this );
        var id     = $btn.data( 'id' );
        var $row   = $btn.closest( 'tr' );
        var $badge = $row.find( '.dd-status-badge' );

        $btn.prop( 'disabled', true ).css( 'opacity', '.5' );

        $.post( DD.ajaxUrl, { action: 'dd_toggle_status', nonce: DD.nonce, id: id })
            .done( function( res ) {
                if ( res.success ) {
                    var active = res.data.status === 1;
                    $badge
                        .text( active ? 'Active' : 'Inactive' )
                        .removeClass( 'dd-status-active dd-status-inactive' )
                        .addClass( active ? 'dd-status-active' : 'dd-status-inactive' );
                    $btn.text( active ? 'Deactivate' : 'Activate' ).data( 'status', res.data.status ).css( 'opacity', '1' );
                    $row.toggleClass( 'dd-row-inactive', ! active );
                    showNotice( 'success', active ? DD.i18n.activated : DD.i18n.deactivated );
                } else {
                    showNotice( 'error', DD.i18n.error );
                }
            })
            .fail( function() { showNotice( 'error', DD.i18n.error ); })
            .always( function() { $btn.prop( 'disabled', false ).css( 'opacity', '1' ); });
    });


}( jQuery ) );
