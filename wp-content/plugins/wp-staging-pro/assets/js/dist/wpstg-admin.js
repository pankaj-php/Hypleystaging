'use strict';
/**
 * Show warning during cloning or push process when closing tab or browser, or changing page
 * @param {beforeunload} event
 * @return {null}
 */

function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var wpstgWarnIfClose = function wpstgWarnIfClose(event) {
  // Only some browsers shows the message below, most say something like "Changes you made may not be saved" (Chrome) or "You have unsaved changes. Exit?"
  event.returnValue = 'You MUST leave this window open while cloning/pushing. Please wait...';
  return null;
};

var WPStaging = function ($) {
  var that = {
    isCancelled: false,
    isFinished: false,
    getLogs: false,
    time: 1,
    executionTime: false,
    progressBar: 0
  };
  var cache = {
    elements: []
  };
  var timeout;
  var ajaxSpinner;
  /**
     * Get / Set Cache for Selector
     * @param {String} selector
     * @return {*}
     */

  cache.get = function (selector) {
    // It is already cached!
    if ($.inArray(selector, cache.elements) !== -1) {
      return cache.elements[selector];
    } // Create cache and return


    cache.elements[selector] = jQuery(selector);
    return cache.elements[selector];
  };
  /**
     * Refreshes given cache
     * @param {String} selector
     */


  cache.refresh = function (selector) {
    selector.elements[selector] = jQuery(selector);
  };
  /**
     * Show and Log Error Message
     * @param {String} message
     */


  var showError = function showError(message) {
    cache.get('#wpstg-try-again').css('display', 'inline-block');
    cache.get('#wpstg-cancel-cloning').text('Reset');
    cache.get('#wpstg-resume-cloning').show();
    cache.get('#wpstg-error-wrapper').show();
    cache.get('#wpstg-error-details').show().html(message);
    cache.get('#wpstg-removing-clone').removeClass('loading');
    cache.get('.wpstg-loader').hide();
    $('.wpstg--modal--process--generic-problem').show().html(message);
  };
  /**
     *
     * @param obj
     * @return {boolean}
     */


  function isEmpty(obj) {
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        return false;
      }
    }

    return true;
  }
  /**
     *
     * @param response the error object
     * @param prependMessage Overwrite default error message at beginning
     * @param appendMessage Overwrite default error message at end
     * @returns void
     */


  var showAjaxFatalError = function showAjaxFatalError(response, prependMessage, appendMessage) {
    prependMessage = prependMessage ? prependMessage + '<br/><br/>' : 'Something went wrong! <br/><br/>';
    appendMessage = appendMessage ? appendMessage + '<br/><br/>' : '<br/><br/>Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.';

    if (response === false) {
      showError(prependMessage + ' Error: No response.' + appendMessage);
      window.removeEventListener('beforeunload', wpstgWarnIfClose);
      return;
    }

    if (typeof response.error !== 'undefined' && response.error) {
      console.error(response.message);
      showError(prependMessage + ' Error: ' + response.message + appendMessage);
      window.removeEventListener('beforeunload', wpstgWarnIfClose);
      return;
    }
  };
  /**
     *
     * @param response
     * @return {{ok}|*}
     */


  var handleFetchErrors = function handleFetchErrors(response) {
    if (!response.ok) {
      showError('Error: ' + response.status + ' - ' + response.statusText + '. Please try again or contact support.');
    }

    return response;
  };
  /** Hide and reset previous thrown visible errors */


  var resetErrors = function resetErrors() {
    cache.get('#wpstg-error-details').hide().html('');
  };

  var slugify = function slugify(url) {
    return url.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, '-').replace(/&/g, '-and-').replace(/[^a-z0-9\-]/g, '').replace(/-+/g, '-').replace(/^-*/, '').replace(/-*$/, '');
  };
  /**
     * Common Elements
     */


  var elements = function elements() {
    var $workFlow = cache.get('#wpstg-workflow');
    var isAllChecked = true;
    var urlSpinner = ajaxurl.replace('/admin-ajax.php', '') + '/images/spinner';
    var timer;

    if (2 < window.devicePixelRatio) {
      urlSpinner += '-2x';
    }

    urlSpinner += '.gif';
    ajaxSpinner = '<img src=\'\'' + urlSpinner + '\' alt=\'\' class=\'ajax-spinner general-spinner\' />';

    var getBaseValues = function getBaseValues() {
      var path = $('#wpstg-use-target-dir').data('base-path');
      var uri = $('#wpstg-use-target-hostname').data('base-uri');
      return {
        path: path
      };
    };

    $workFlow // Check / Un-check All Database Tables New
    .on('click', '.wpstg-button-unselect', function (e) {
      e.preventDefault();

      if (false === isAllChecked) {
        console.log('true');
        cache.get('#wpstg_select_tables_cloning .wpstg-db-table').prop('selected', 'selected');
        cache.get('.wpstg-button-unselect').text('Unselect All');
        cache.get('.wpstg-db-table-checkboxes').prop('checked', true);
        isAllChecked = true;
      } else {
        console.log('false');
        cache.get('#wpstg_select_tables_cloning .wpstg-db-table').prop('selected', false);
        cache.get('.wpstg-button-unselect').text('Select All');
        cache.get('.wpstg-db-table-checkboxes').prop('checked', false);
        isAllChecked = false;
      }
    })
    /**
             * Select tables with certain tbl prefix | NEW
             * @param obj e
             * @returns {undefined}
             */
    .on('click', '.wpstg-button-select', function (e) {
      e.preventDefault();
      $('#wpstg_select_tables_cloning .wpstg-db-table').each(function () {
        if (wpstg.isMultisite == 1) {
          if ($(this).attr('name').match('^' + wpstg.tblprefix + '([^0-9])_*')) {
            $(this).prop('selected', 'selected');
          } else {
            $(this).prop('selected', false);
          }
        }

        if (wpstg.isMultisite == 0) {
          if ($(this).attr('name').match('^' + wpstg.tblprefix)) {
            $(this).prop('selected', 'selected');
          } else {
            $(this).prop('selected', false);
          }
        }
      });
    }) // Expand Directories
    .on('click', '.wpstg-expand-dirs', function (e) {
      e.preventDefault();
      var $this = $(this);

      if (!$this.hasClass('disabled')) {
        $this.siblings('.wpstg-subdir').slideToggle();
      }
    }) // When a directory checkbox is Selected
    .on('change', 'input.wpstg-check-dir', function () {
      var $directory = $(this).parent('.wpstg-dir');

      if (this.checked) {
        $directory.parents('.wpstg-dir').children('.wpstg-check-dir').prop('checked', true);
        $directory.find('.wpstg-expand-dirs').removeClass('disabled');
        $directory.find('.wpstg-subdir .wpstg-check-dir').prop('checked', true);
      } else {
        $directory.find('.wpstg-dir .wpstg-check-dir').prop('checked', false);
        $directory.find('.wpstg-expand-dirs, .wpstg-check-subdirs').addClass('disabled');
        $directory.find('.wpstg-check-subdirs').data('action', 'check').text('check');
      }
    }) // When a directory name is Selected
    .on('change', 'href.wpstg-check-dir', function () {
      var $directory = $(this).parent('.wpstg-dir');

      if (this.checked) {
        $directory.parents('.wpstg-dir').children('.wpstg-check-dir').prop('checked', true);
        $directory.find('.wpstg-expand-dirs').removeClass('disabled');
        $directory.find('.wpstg-subdir .wpstg-check-dir').prop('checked', true);
      } else {
        $directory.find('.wpstg-dir .wpstg-check-dir').prop('checked', false);
        $directory.find('.wpstg-expand-dirs, .wpstg-check-subdirs').addClass('disabled');
        $directory.find('.wpstg-check-subdirs').data('action', 'check').text('check');
      }
    }) // Check the max length of the clone name and if the clone name already exists
    .on('keyup', '#wpstg-new-clone-id', function () {
      // Hide previous errors
      document.getElementById('wpstg-error-details').style.display = 'none'; // This request was already sent, clear it up!

      if ('number' === typeof timer) {
        clearInterval(timer);
      }

      var cloneID = this.value;
      timer = setTimeout(function () {
        ajax({
          action: 'wpstg_check_clone',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          cloneID: cloneID
        }, function (response) {
          if (response.status === 'success') {
            cache.get('#wpstg-new-clone-id').removeClass('wpstg-error-input');
            cache.get('#wpstg-start-cloning').removeAttr('disabled');
            cache.get('#wpstg-clone-id-error').text('').hide();
          } else {
            cache.get('#wpstg-new-clone-id').addClass('wpstg-error-input');
            cache.get('#wpstg-start-cloning').prop('disabled', true);
            cache.get('#wpstg-clone-id-error').text(response.message).show();
          }
        });
      }, 500);
    }) // Restart cloning process
    .on('click', '#wpstg-start-cloning', function () {
      resetErrors();
      that.isCancelled = false;
      that.getLogs = false;
      that.progressBar = 0;
    }).on('input', '#wpstg-new-clone-id', function () {
      if ($('#wpstg-clone-directory').length < 1) {
        return;
      }

      var slug = slugify(this.value);
      var $targetDir = $('#wpstg-use-target-dir');
      var $targetUri = $('#wpstg-use-target-hostname');
      var path = $targetDir.data('base-path');
      var uri = $targetUri.data('base-uri');

      if (path) {
        path = path.replace(/\/+$/g, '') + '/' + slug + '/';
      }

      if (uri) {
        uri = uri.replace(/\/+$/g, '') + '/' + slug;
      }

      $('.wpstg-use-target-dir--value').text(path);
      $('.wpstg-use-target-hostname--value').text(uri);
      $targetDir.attr('data-path', path);
      $targetUri.attr('data-uri', uri);
      $('#wpstg_clone_dir').attr('placeholder', path);
      $('#wpstg_clone_hostname').attr('placeholder', uri);
    }).on('input', '#wpstg_clone_hostname', function () {
      if ($(this).val() === '' || validateTargetHost()) {
        $('#wpstg_clone_hostname_error').remove();
        return;
      }

      if (!validateTargetHost() && !$('#wpstg_clone_hostname_error').length) {
        $('#wpstg-clone-directory tr:last-of-type').after('<tr><td>&nbsp;</td><td><p id="wpstg_clone_hostname_error" style="color: red;">&nbsp;Invalid host name. Please provide it in a format like http://example.com</p></td></tr>');
      }
    });
    cloneActions();
  };
  /* @returns {boolean} */


  var validateTargetHost = function validateTargetHost() {
    var the_domain = $('#wpstg_clone_hostname').val();

    if (the_domain === '') {
      return true;
    }

    var reg = /^http(s)?:\/\/.*$/;

    if (reg.test(the_domain) === false) {
      return false;
    }

    return true;
  };
  /**
     * Clone actions
     */


  var cloneActions = function cloneActions() {
    var $workFlow = cache.get('#wpstg-workflow');
    $workFlow // Cancel cloning
    .on('click', '#wpstg-cancel-cloning', function () {
      if (!confirm('Are you sure you want to cancel cloning process?')) {
        return false;
      }

      var $this = $(this);
      $('#wpstg-try-again, #wpstg-home-link').hide();
      $this.prop('disabled', true);
      that.isCancelled = true;
      that.progressBar = 0;
      $('#wpstg-processing-status').text('Please wait...this can take up a while.');
      $('.wpstg-loader, #wpstg-show-log-button').hide();
      $this.parent().append(ajaxSpinner);
      cancelCloning();
    }) // Resume cloning
    .on('click', '#wpstg-resume-cloning', function () {
      resetErrors();
      var $this = $(this);
      $('#wpstg-try-again, #wpstg-home-link').hide();
      that.isCancelled = false;
      $('#wpstg-processing-status').text('Try to resume cloning process...');
      $('#wpstg-error-details').hide();
      $('.wpstg-loader').show();
      $this.parent().append(ajaxSpinner);
      that.startCloning();
    }) // Cancel update cloning
    .on('click', '#wpstg-cancel-cloning-update', function () {
      resetErrors();
      var $this = $(this);
      $('#wpstg-try-again, #wpstg-home-link').hide();
      $this.prop('disabled', true);
      that.isCancelled = true;
      $('#wpstg-cloning-result').text('Please wait...this can take up a while.');
      $('.wpstg-loader, #wpstg-show-log-button').hide();
      $this.parent().append(ajaxSpinner);
      cancelCloningUpdate();
    }) // Restart cloning
    .on('click', '#wpstg-restart-cloning', function () {
      resetErrors();
      var $this = $(this);
      $('#wpstg-try-again, #wpstg-home-link').hide();
      $this.prop('disabled', true);
      that.isCancelled = true;
      $('#wpstg-cloning-result').text('Please wait...this can take up a while.');
      $('.wpstg-loader, #wpstg-show-log-button').hide();
      $this.parent().append(ajaxSpinner);
      restart();
    }) // Delete clone - confirmation
    .on('click', '.wpstg-remove-clone[data-clone]', function (e) {
      resetErrors();
      e.preventDefault();
      var $existingClones = cache.get('#wpstg-existing-clones');
      $workFlow.removeClass('active');
      cache.get('.wpstg-loader').show();
      ajax({
        action: 'wpstg_confirm_delete_clone',
        accessToken: wpstg.accessToken,
        nonce: wpstg.nonce,
        clone: $(this).data('clone')
      }, function (response) {
        cache.get('#wpstg-removing-clone').html(response);
        $existingClones.children('img').remove();
        cache.get('.wpstg-loader').hide();
        $('html, body').animate({
          // This logic is meant to be a "scrollBottom"
          scrollTop: $('#wpstg-remove-clone').offset().top - $(window).height() + $('#wpstg-remove-clone').height() + 50
        }, 100);
      }, 'HTML');
    }) // Delete clone - confirmed
    .on('click', '#wpstg-remove-clone', function (e) {
      resetErrors();
      e.preventDefault();
      cache.get('#wpstg-removing-clone').addClass('loading');
      cache.get('.wpstg-loader').show();
      deleteClone($(this).data('clone'));
    }) // Cancel deleting clone
    .on('click', '#wpstg-cancel-removing', function (e) {
      e.preventDefault();
      $('.wpstg-clone').removeClass('active');
      cache.get('#wpstg-removing-clone').html('');
    }) // Update
    .on('click', '.wpstg-execute-clone', function (e) {
      e.preventDefault();
      var clone = $(this).data('clone');
      $workFlow.addClass('loading');
      ajax({
        action: 'wpstg_scanning',
        clone: clone,
        accessToken: wpstg.accessToken,
        nonce: wpstg.nonce
      }, function (response) {
        if (response.length < 1) {
          showError('Something went wrong! Error: No response.  Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
        }

        $workFlow.removeClass('loading').html(response); // register check disk space function for clone update process.

        checkDiskSpace();
        that.switchStep(2);
      }, 'HTML');
    }) // Reset Clone
    .on('click', '.wpstg-reset-clone', function (e) {
      e.preventDefault();
      var clone = $(this).data('clone');
      Swal.fire({
        title: '',
        icon: 'warning',
        html: 'Do you really want to reset this staging site with the current state of the production site? <br> <span style="color:red;">This will delete all your modifications!</span>',
        width: '650px',
        focusConfirm: false,
        customClass: {
          confirmButton: 'wpstg-confirm-reset-clone'
        },
        confirmButtonText: 'Reset Clone',
        showCancelButton: true
      }).then(function (result) {
        if (result.value) {
          resetClone(clone);
        }
      });
      return;
    });
  };
  /**
     * Ajax Requests
     * @param Object data
     * @param Function callback
     * @param string dataType
     * @param bool showErrors
     * @param int tryCount
     * @param float incrementRatio
     */


  var ajax = function ajax(data, callback, dataType, showErrors, tryCount) {
    var incrementRatio = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : null;

    if ('undefined' === typeof dataType) {
      dataType = 'json';
    }

    if (false !== showErrors) {
      showErrors = true;
    }

    tryCount = 'undefined' === typeof tryCount ? 0 : tryCount;
    var retryLimit = 10;
    var retryTimeout = 10000 * tryCount;
    incrementRatio = parseInt(incrementRatio);

    if (!isNaN(incrementRatio)) {
      retryTimeout *= incrementRatio;
    }

    $.ajax({
      url: ajaxurl + '?action=wpstg_processing&_=' + Date.now() / 1000,
      type: 'POST',
      dataType: dataType,
      cache: false,
      data: data,
      error: function error(xhr, textStatus, errorThrown) {
        console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus); // try again after 10 seconds

        tryCount++;

        if (tryCount <= retryLimit) {
          setTimeout(function () {
            ajax(data, callback, dataType, showErrors, tryCount, incrementRatio);
            return;
          }, retryTimeout);
        } else {
          var errorCode = 'undefined' === typeof xhr.status ? 'Unknown' : xhr.status;
          showError('Fatal Error:  ' + errorCode + ' Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
        }
      },
      success: function success(data) {
        if ('function' === typeof callback) {
          callback(data);
        }
      },
      statusCode: {
        404: function _() {
          if (tryCount >= retryLimit) {
            showError('Error 404 - Can\'t find ajax request URL! Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
          }
        },
        500: function _() {
          if (tryCount >= retryLimit) {
            showError('Fatal Error 500 - Internal server error while processing the request! Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.');
          }
        },
        504: function _() {
          if (tryCount > retryLimit) {
            showError('Error 504 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
          }
        },
        502: function _() {
          if (tryCount >= retryLimit) {
            showError('Error 502 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
          }
        },
        503: function _() {
          if (tryCount >= retryLimit) {
            showError('Error 503 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
          }
        },
        429: function _() {
          if (tryCount >= retryLimit) {
            showError('Error 429 - It looks like your server is rate limiting ajax requests. Please try to resume after a minute. If this still not works try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report and contact us.\n\ ');
          }
        },
        403: function _() {
          if (tryCount >= retryLimit) {
            showError('Refresh page or login again! The process should be finished successfully. \n\ ');
          }
        }
      }
    });
  };
  /**
     * Next / Previous Step Clicks to Navigate Through Staging Job
     */


  var stepButtons = function stepButtons() {
    var $workFlow = cache.get('#wpstg-workflow');
    $workFlow // Next Button
    .on('click', '.wpstg-next-step-link', function (e) {
      e.preventDefault();
      var $this = $(this);
      var isScan = false;

      if ($('#wpstg_clone_hostname').length && !validateTargetHost()) {
        $('#wpstg_clone_hostname').focus();
        return false;
      }

      if ($this.data('action') === 'wpstg_update' || $this.data('action') === 'wpstg_reset') {
        // Update / Reset Clone - confirmed
        var onlyUpdateMessage = '';

        if ($this.data('action') === 'wpstg_update') {
          onlyUpdateMessage = ' \n\nExclude all tables and folders you do not want to overwrite, first! \n\nDo not cancel the updating process! This can break your staging site. \n\n\Create a backup of your staging website before you proceed.';
        }

        if (!confirm('STOP! This will overwrite your staging site with all selected data from the production site! This should be used only if you want to clone again your production site. Are you sure you want to do this?' + onlyUpdateMessage)) {
          return false;
        }
      } // Button is disabled


      if ($this.attr('disabled')) {
        return false;
      }

      if ($this.data('action') === 'wpstg_cloning') {
        // Verify External Database If Checked and Not Skipped
        if ($('#wpstg-ext-db').is(':checked')) {
          verifyExternalDatabase($this, $workFlow);
          return;
        }
      }

      proceedCloning($this, $workFlow);
    }) // Previous Button
    .on('click', '.wpstg-prev-step-link', function (e) {
      e.preventDefault();
      cache.get('.wpstg-loader').removeClass('wpstg-finished');
      cache.get('.wpstg-loader').hide();
      loadOverview();
    });
  };
  /**
     * Get Included (Checked) Database Tables
     * @return {Array}
     */


  var getIncludedTables = function getIncludedTables() {
    var includedTables = [];
    $('#wpstg_select_tables_cloning option:selected').each(function () {
      includedTables.push(this.value);
    });
    return includedTables;
  };
  /**
     * Get Excluded (Unchecked) Database Tables
     * Not used anymore!
     * @return {Array}
     */


  var getExcludedTables = function getExcludedTables() {
    var excludedTables = [];
    $('.wpstg-db-table input:not(:checked)').each(function () {
      excludedTables.push(this.name);
    });
    return excludedTables;
  };
  /**
     * Get Included Directories
     * @return {Array}
     */


  var getIncludedDirectories = function getIncludedDirectories() {
    var includedDirectories = [];
    $('.wpstg-dir input:checked.wpstg-root').each(function () {
      var $this = $(this);
      includedDirectories.push(encodeURIComponent($this.val()));
    });
    return includedDirectories;
  };
  /**
     * Get Excluded Directories
     * @return {Array}
     */


  var getExcludedDirectories = function getExcludedDirectories() {
    var excludedDirectories = [];
    $('.wpstg-dir input:not(:checked).wpstg-root').each(function () {
      var $this = $(this);
      excludedDirectories.push(encodeURIComponent($this.val()));
    });
    return excludedDirectories;
  };
  /**
     * Get included extra directories of the root level
     * All directories except wp-content, wp-admin, wp-includes
     * @return {Array}
     */


  var getIncludedExtraDirectories = function getIncludedExtraDirectories() {
    // Add directories from the root level
    var extraDirectories = [];
    $('.wpstg-dir input:checked.wpstg-extra').each(function () {
      var $this = $(this);
      extraDirectories.push(encodeURIComponent($this.val()));
    }); // Add any other custom selected extra directories

    if (!$('#wpstg_extraDirectories').val()) {
      return extraDirectories;
    }

    var extraCustomDirectories = encodeURIComponent($('#wpstg_extraDirectories').val().split(/\r?\n/));
    return extraDirectories.concat(extraCustomDirectories);
  };
  /**
     * Verify External Database for Cloning
     */


  var verifyExternalDatabase = function verifyExternalDatabase($this, workflow) {
    cache.get('.wpstg-loader').show();
    ajax({
      action: 'wpstg_database_verification',
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce,
      databaseUser: cache.get('#wpstg_db_username').val(),
      databasePassword: cache.get('#wpstg_db_password').val(),
      databaseServer: cache.get('#wpstg_db_server').val(),
      databaseDatabase: cache.get('#wpstg_db_database').val()
    }, function (response) {
      // Undefined Error
      if (false === response) {
        showError('Something went wrong! Error: No response.' + 'Please try again. If that does not help, ' + '<a href=\'https://wp-staging.com/support/\' target=\'_blank\'>open a support ticket</a> ');
        cache.get('.wpstg-loader').hide();
        return;
      } // Throw Error


      if ('undefined' === typeof response.success) {
        showError('Something went wrong! Error: Invalid response.' + 'Please try again. If that does not help, ' + '<a href=\'https://wp-staging.com/support/\' target=\'_blank\'>open a support ticket</a> ');
        cache.get('.wpstg-loader').hide();
        return;
      }

      if (response.success) {
        cache.get('.wpstg-loader').hide();
        proceedCloning($this, workflow);
        return;
      }

      if (response.error_type === 'comparison') {
        cache.get('.wpstg-loader').hide();
        var render = '<table style="width: 100%;"><thead><tr><th>Property</th><th>Production DB</th><th>Staging DB</th><th>Status</th></tr></thead><tbody>';
        response.checks.forEach(function (x) {
          var icon = '<i style="color: #00ff00">✔</i>';

          if (x.production !== x.staging) {
            icon = '<i style="color: #ff0000">❌</i>';
          }

          render += '<tr><td>' + x.name + '</td><td>' + x.production + '</td><td>' + x.staging + '</td><td>' + icon + '</td></tr>';
        });
        render += '</tbody></table><p>Note: Some mySQL properties do not match. You may proceed but the staging site may not work as expected.</p>';
        Swal.fire({
          title: 'Different Database Properties',
          icon: 'warning',
          html: render,
          width: '650px',
          focusConfirm: false,
          confirmButtonText: 'Proceed Anyway',
          showCancelButton: true
        }).then(function (result) {
          if (result.value) {
            proceedCloning($this, workflow);
          }
        });
        return;
      }

      Swal.fire({
        title: 'Different Database Properties',
        icon: 'error',
        html: response.message,
        focusConfirm: true,
        confirmButtonText: 'Ok',
        showCancelButton: false
      });
      cache.get('.wpstg-loader').hide();
    }, 'json', false);
  };
  /**
     * Get Cloning Step Data
     */


  var getCloningData = function getCloningData() {
    if ('wpstg_cloning' !== that.data.action && 'wpstg_update' !== that.data.action && 'wpstg_reset' !== that.data.action) {
      return;
    }

    that.data.cloneID = $('#wpstg-new-clone-id').val() || new Date().getTime().toString(); // Remove this to keep &_POST[] small otherwise mod_security will throw error 404
    // that.data.excludedTables = getExcludedTables();

    that.data.includedTables = getIncludedTables();
    that.data.includedDirectories = getIncludedDirectories();
    that.data.excludedDirectories = getExcludedDirectories();
    that.data.extraDirectories = getIncludedExtraDirectories();
    that.data.databaseServer = $('#wpstg_db_server').val();
    that.data.databaseUser = $('#wpstg_db_username').val();
    that.data.databasePassword = $('#wpstg_db_password').val();
    that.data.databaseDatabase = $('#wpstg_db_database').val();
    that.data.databasePrefix = $('#wpstg_db_prefix').val();
    var cloneDir = $('#wpstg_clone_dir').val();
    that.data.cloneDir = encodeURIComponent($.trim(cloneDir));
    that.data.cloneHostname = $('#wpstg_clone_hostname').val();
    that.data.emailsAllowed = $('#wpstg_allow_emails').is(':checked');
    that.data.uploadsSymlinked = $('#wpstg_symlink_upload').is(':checked');
    that.data.cleanPluginsThemes = $('#wpstg-clean-plugins-themes').is(':checked');
    that.data.cleanUploadsDir = $('#wpstg-clean-uploads').is(':checked');
  };

  var proceedCloning = function proceedCloning($this, workflow) {
    // Add loading overlay
    workflow.addClass('loading'); // Prepare data

    that.data = {
      action: $this.data('action'),
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce
    }; // Cloning data

    getCloningData();
    console.log(that.data);
    sendCloningAjax(workflow);
  };

  var sendCloningAjax = function sendCloningAjax(workflow) {
    // Send ajax request
    ajax(that.data, function (response) {
      // Undefined Error
      if (false === response) {
        showError('Something went wrong!<br/><br/> Go to WP Staging > Settings and lower \'File Copy Limit\' and \'DB Query Limit\'. Also set \'CPU Load Priority to low \'' + 'and try again. If that does not help, ' + '<a href=\'https://wp-staging.com/support/\' target=\'_blank\'>open a support ticket</a> ');
      }

      if (response.length < 1) {
        showError('Something went wrong! No response.  Go to WP Staging > Settings and lower \'File Copy Limit\' and \'DB Query Limit\'. Also set \'CPU Load Priority to low \'' + 'and try again. If that does not help, ' + '<a href=\'https://wp-staging.com/support/\' target=\'_blank\'>open a support ticket</a> ');
      } // Styling of elements


      workflow.removeClass('loading').html(response);

      if (that.data.action === 'wpstg_scanning') {
        that.switchStep(2);
      } else if (that.data.action === 'wpstg_cloning' || that.data.action === 'wpstg_update' || that.data.action === 'wpstg_reset') {
        that.switchStep(3);
      } // Start cloning


      that.startCloning();
    }, 'HTML');
  };

  var resetClone = function resetClone(clone) {
    that.data = {
      action: 'wpstg_reset',
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce,
      cloneID: clone
    };
    var $workFlow = cache.get('#wpstg-workflow');
    sendCloningAjax($workFlow);
  };
  /**
     * Loads Overview (first step) of Staging Job
     */


  var loadOverview = function loadOverview() {
    var $workFlow = cache.get('#wpstg-workflow');
    $workFlow.addClass('loading');
    ajax({
      action: 'wpstg_overview',
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce
    }, function (response) {
      if (response.length < 1) {
        showError('Something went wrong! No response. Please try the <a href=\'https://wp-staging.com/docs/wp-staging-settings-for-small-servers/\' target=\'_blank\'>WP Staging Small Server Settings</a> or submit an error report.');
      }

      var $currentStep = cache.get('.wpstg-current-step'); // Styling of elements

      $workFlow.removeClass('loading').html(response);
    }, 'HTML');
    that.switchStep(1);
    cache.get('.wpstg-step3-cloning').show();
    cache.get('.wpstg-step3-pushing').hide();
  };
  /**
     * Load Tabs
     */


  var tabs = function tabs() {
    cache.get('#wpstg-workflow').on('click', '.wpstg-tab-header', function (e) {
      e.preventDefault();
      var $this = $(this);
      var $section = cache.get($this.data('id'));
      $this.toggleClass('expand');
      $section.slideToggle();

      if ($this.hasClass('expand')) {
        $this.find('.wpstg-tab-triangle').html('&#9660;');
      } else {
        $this.find('.wpstg-tab-triangle').html('&#9658;');
      }
    });
  };
  /**
     * Delete Clone
     * @param {String} clone
     */


  var deleteClone = function deleteClone(clone) {
    var deleteDir = $('#deleteDirectory:checked').data('deletepath');
    ajax({
      action: 'wpstg_delete_clone',
      clone: clone,
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce,
      excludedTables: getExcludedTables(),
      deleteDir: deleteDir
    }, function (response) {
      if (response) {
        showAjaxFatalError(response); // Finished

        if ('undefined' !== typeof response["delete"] && (response["delete"] === 'finished' || response["delete"] === 'unfinished')) {
          cache.get('#wpstg-removing-clone').removeClass('loading').html('');

          if (response["delete"] === 'finished') {
            $('.wpstg-clone#' + clone).remove();
          }

          if ($('.wpstg-clone').length < 1) {
            cache.get('#wpstg-existing-clones').find('h3').text('');
          }

          cache.get('.wpstg-loader').hide();
          return;
        }
      } // continue


      if (true !== response) {
        deleteClone(clone);
        return;
      }
    });
  };
  /**
     * Cancel Cloning Process
     */


  var cancelCloning = function cancelCloning() {
    that.timer('stop');

    if (true === that.isFinished) {
      return true;
    }

    ajax({
      action: 'wpstg_cancel_clone',
      clone: that.data.cloneID,
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce
    }, function (response) {
      if (response && 'undefined' !== typeof response["delete"] && response["delete"] === 'finished') {
        cache.get('.wpstg-loader').hide(); // Load overview

        loadOverview();
        return;
      }

      if (true !== response) {
        // continue
        cancelCloning();
        return;
      } // Load overview


      loadOverview();
    });
  };
  /**
     * Cancel Cloning Process
     */


  var cancelCloningUpdate = function cancelCloningUpdate() {
    if (true === that.isFinished) {
      return true;
    }

    ajax({
      action: 'wpstg_cancel_update',
      clone: that.data.cloneID,
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce
    }, function (response) {
      if (response && 'undefined' !== typeof response["delete"] && response["delete"] === 'finished') {
        // Load overview
        loadOverview();
        return;
      }

      if (true !== response) {
        // continue
        cancelCloningUpdate();
        return;
      } // Load overview


      loadOverview();
    });
  };
  /**
     * Cancel Cloning Process
     */


  var restart = function restart() {
    if (true === that.isFinished) {
      return true;
    }

    ajax({
      action: 'wpstg_restart',
      // clone: that.data.cloneID,
      accessToken: wpstg.accessToken,
      nonce: wpstg.nonce
    }, function (response) {
      if (response && 'undefined' !== typeof response["delete"] && response["delete"] === 'finished') {
        // Load overview
        loadOverview();
        return;
      }

      if (true !== response) {
        // continue
        cancelCloningUpdate();
        return;
      } // Load overview


      loadOverview();
    });
  };
  /**
     * Scroll the window log to bottom
     * @return void
     */


  var logscroll = function logscroll() {
    var $div = cache.get('.wpstg-log-details');

    if ('undefined' !== typeof $div[0]) {
      $div.scrollTop($div[0].scrollHeight);
    }
  };
  /**
     * Append the log to the logging window
     * @param string log
     * @return void
     */


  var getLogs = function getLogs(log) {
    if (log != null && 'undefined' !== typeof log) {
      if (log.constructor === Array) {
        $.each(log, function (index, value) {
          if (value === null) {
            return;
          }

          if (value.type === 'ERROR') {
            cache.get('.wpstg-log-details').append('<span style="color:red;">[' + value.type + ']</span>-' + '[' + value.date + '] ' + value.message + '</br>');
          } else {
            cache.get('.wpstg-log-details').append('[' + value.type + ']-' + '[' + value.date + '] ' + value.message + '</br>');
          }
        });
      } else {
        cache.get('.wpstg-log-details').append('[' + log.type + ']-' + '[' + log.date + '] ' + log.message + '</br>');
      }
    }

    logscroll();
  };
  /**
     * Check diskspace
     * @return string json
     */


  var checkDiskSpace = function checkDiskSpace() {
    cache.get('#wpstg-check-space').on('click', function (e) {
      cache.get('.wpstg-loader').show();
      console.log('check disk space');
      ajax({
        action: 'wpstg_check_disk_space',
        accessToken: wpstg.accessToken,
        nonce: wpstg.nonce
      }, function (response) {
        if (false === response) {
          cache.get('#wpstg-clone-id-error').text('Can not detect required disk space').show();
          cache.get('.wpstg-loader').hide();
          return;
        } // Show required disk space


        cache.get('#wpstg-clone-id-error').html('Estimated necessary disk space: ' + response.usedspace + '<br> <span style="color:#444;">Before you proceed ensure your account has enough free disk space to hold the entire instance of the production site. You can check the available space from your hosting account (cPanel or similar).</span>').show();
        cache.get('.wpstg-loader').hide();
      }, 'json', false);
    });
  };

  var mainTabs = function mainTabs() {
    $('.wpstg--tab--header a[data-target]').on('click', function () {
      var $this = $(this);
      var target = $this.attr('data-target');
      var $wrapper = $this.parents('.wpstg--tab--wrapper');
      var $menuItems = $wrapper.find('.wpstg--tab--header a[data-target]');
      var $contents = $wrapper.find('.wpstg--tab--contents > .wpstg--tab--content');
      $contents.filter('.wpstg--tab--active:not(.wpstg--tab--active' + target + ')').removeClass('wpstg--tab--active');
      $menuItems.not($this).removeClass('wpstg--tab--active');
      $this.addClass('wpstg--tab--active');
      $(target).addClass('wpstg--tab--active');

      if ('#wpstg--tab--snapshot' === target) {
        that.snapshots.init();
      }
    });
  };
  /**
     * Show or hide animated loading icon
     * @param isLoading bool
     */


  var isLoading = function isLoading(_isLoading) {
    if (!_isLoading || _isLoading === false) {
      cache.get('.wpstg-loader').hide();
    } else {
      cache.get('.wpstg-loader').show();
    }
  };
  /**
     * Count up processing execution time
     * @param string status
     * @return html
     */


  that.timer = function (status) {
    if (status === 'stop') {
      var time = that.time;
      that.time = 1;
      clearInterval(that.executionTime);
      return that.convertSeconds(time);
    }

    that.executionTime = setInterval(function () {
      if (null !== document.getElementById('wpstg-processing-timer')) {
        document.getElementById('wpstg-processing-timer').innerHTML = 'Elapsed Time: ' + that.convertSeconds(that.time);
      }

      that.time++;

      if (status === 'stop') {
        that.time = 1;
        clearInterval(that.executionTime);
      }
    }, 1000);
  };
  /**
     * Convert seconds to hourly format
     * @param int seconds
     * @return string
     */


  that.convertSeconds = function (seconds) {
    var date = new Date(null);
    date.setSeconds(seconds); // specify value for SECONDS here

    return date.toISOString().substr(11, 8);
  };
  /**
     * Start Cloning Process
     * @type {Function}
     */


  that.startCloning = function () {
    resetErrors(); // Register function for checking disk space

    checkDiskSpace();

    if ('wpstg_cloning' !== that.data.action && 'wpstg_update' !== that.data.action && 'wpstg_reset' !== that.data.action) {
      return;
    }

    that.isCancelled = false; // Start the process

    start(); // Functions
    // Start

    function start() {
      console.log('Starting cloning process...');
      cache.get('.wpstg-loader').show();
      cache.get('#wpstg-cancel-cloning').text('Cancel');
      cache.get('#wpstg-resume-cloning').hide();
      cache.get('#wpstg-error-details').hide(); // Clone Database

      setTimeout(function () {
        // cloneDatabase();
        window.addEventListener('beforeunload', wpstgWarnIfClose);
        processing();
      }, wpstg.delayReq);
      that.timer('start');
    }
    /**
         * Start ajax processing
         * @return string
         */


    var processing = function processing() {
      if (true === that.isCancelled) {
        window.removeEventListener('beforeunload', wpstgWarnIfClose);
        return false;
      }

      isLoading(true); // Show logging window

      cache.get('.wpstg-log-details').show();
      WPStaging.ajax({
        action: 'wpstg_processing',
        accessToken: wpstg.accessToken,
        nonce: wpstg.nonce,
        excludedTables: getExcludedTables(),
        includedDirectories: getIncludedDirectories(),
        excludedDirectories: getExcludedDirectories(),
        extraDirectories: getIncludedExtraDirectories()
      }, function (response) {
        showAjaxFatalError(response); // Add Log messages

        if ('undefined' !== typeof response.last_msg && response.last_msg) {
          getLogs(response.last_msg);
        } // Continue processing


        if (false === response.status) {
          progressBar(response);
          setTimeout(function () {
            cache.get('.wpstg-loader').show();
            processing();
          }, wpstg.delayReq);
        } else if (true === response.status && 'finished' !== response.status) {
          cache.get('#wpstg-error-details').hide();
          cache.get('#wpstg-error-wrapper').hide();
          progressBar(response, true);
          processing();
        } else if ('finished' === response.status || 'undefined' !== typeof response.job_done && response.job_done) {
          window.removeEventListener('beforeunload', wpstgWarnIfClose);
          finish(response);
        }

        ;
      }, 'json', false);
    }; // Finish


    function finish(response) {
      if (true === that.getLogs) {
        getLogs();
      }

      progressBar(response); // Add Log

      if ('undefined' !== typeof response.last_msg) {
        getLogs(response.last_msg);
      }

      console.log('Cloning process finished');
      cache.get('.wpstg-loader').hide();
      cache.get('#wpstg-processing-header').html('Processing Complete');
      $('#wpstg-processing-status').text('Succesfully finished');
      cache.get('#wpstg_staging_name').html(that.data.cloneID);
      cache.get('#wpstg-finished-result').show();
      cache.get('#wpstg-cancel-cloning').hide();
      cache.get('#wpstg-resume-cloning').hide();
      cache.get('#wpstg-cancel-cloning-update').prop('disabled', true);
      var $link1 = cache.get('#wpstg-clone-url-1');
      var $link = cache.get('#wpstg-clone-url');
      $link1.attr('href', response.url);
      $link1.html(response.url);
      $link.attr('href', response.url);
      cache.get('#wpstg-remove-clone').data('clone', that.data.cloneID); // Finished

      that.isFinished = true;
      that.timer('stop');
      cache.get('.wpstg-loader').hide();
      cache.get('#wpstg-processing-header').html('Processing Complete');
      return false;
    }
    /**
         * Add percentage progress bar
         * @param object response
         * @return {Boolean}
         */


    var progressBar = function progressBar(response, restart) {
      if ('undefined' === typeof response.percentage) {
        return false;
      }

      if (response.job === 'database') {
        cache.get('#wpstg-progress-db').width(response.percentage * 0.2 + '%').html(response.percentage + '%');
        cache.get('#wpstg-processing-status').html(response.percentage.toFixed(0) + '%' + ' - Step 1 of 4 Cloning Database Tables...');
      }

      if (response.job === 'SearchReplace') {
        cache.get('#wpstg-progress-db').css('background-color', '#3bc36b');
        cache.get('#wpstg-progress-db').html('1. Database'); // Assumption: All previous steps are done.
        // This avoids bugs where some steps are skipped and the progress bar is incomplete as a result

        cache.get('#wpstg-progress-db').width('20%');
        cache.get('#wpstg-progress-sr').width(response.percentage * 0.1 + '%').html(response.percentage + '%');
        cache.get('#wpstg-processing-status').html(response.percentage.toFixed(0) + '%' + ' - Step 2 of 4 Preparing Database Data...');
      }

      if (response.job === 'directories') {
        cache.get('#wpstg-progress-sr').css('background-color', '#3bc36b');
        cache.get('#wpstg-progress-sr').html('2. Data');
        cache.get('#wpstg-progress-sr').width('10%');
        cache.get('#wpstg-progress-dirs').width(response.percentage * 0.1 + '%').html(response.percentage + '%');
        cache.get('#wpstg-processing-status').html(response.percentage.toFixed(0) + '%' + ' - Step 3 of 4 Getting files...');
      }

      if (response.job === 'files') {
        cache.get('#wpstg-progress-dirs').css('background-color', '#3bc36b');
        cache.get('#wpstg-progress-dirs').html('3. Files');
        cache.get('#wpstg-progress-dirs').width('10%');
        cache.get('#wpstg-progress-files').width(response.percentage * 0.6 + '%').html(response.percentage + '%');
        cache.get('#wpstg-processing-status').html(response.percentage.toFixed(0) + '%' + ' - Step 4 of 4 Copy files...');
      }

      if (response.job === 'finish') {
        cache.get('#wpstg-progress-files').css('background-color', '#3bc36b');
        cache.get('#wpstg-progress-files').html('4. Copy Files');
        cache.get('#wpstg-progress-files').width('60%');
        cache.get('#wpstg-processing-status').html(response.percentage.toFixed(0) + '%' + ' - Cloning Process Finished');
      }
    };
  };

  that.switchStep = function (step) {
    cache.get('.wpstg-current-step').removeClass('wpstg-current-step');
    cache.get('.wpstg-step' + step).addClass('wpstg-current-step');
  };
  /**
     * Initiation
     * @type {Function}
     */


  that.init = function () {
    loadOverview();
    elements();
    stepButtons();
    tabs();
    mainTabs();
  };
  /**
     * Ajax call
     * @type {ajax}
     */


  that.ajax = ajax;
  that.showError = showError;
  that.getLogs = getLogs;
  that.loadOverview = loadOverview; // TODO RPoC (too big, scattered and unorganized)

  that.snapshots = {
    type: null,
    isCancelled: false,
    processInfo: {
      title: null,
      interval: null
    },
    modal: {
      create: {
        html: null,
        confirmBtnTxt: null
      },
      process: {
        html: null,
        cancelBtnTxt: null,
        modal: null
      },
      download: {
        html: null
      },
      "import": {
        html: null,
        btnTxtNext: null,
        btnTxtConfirm: null,
        btnTxtCancel: null,
        searchReplaceForm: null,
        file: null,
        containerUpload: null,
        containerFilesystem: null,
        setFile: function setFile(file) {
          var upload = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

          var toUnit = function toUnit(bytes) {
            var i = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
          };

          if (!file) {
            return;
          }

          that.snapshots.modal["import"].file = file;
          that.snapshots.modal["import"].data.file = file.name;
          console.log("File ".concat(file.name));
          $('.wpstg--snapshot--import--selected-file').html("".concat(file.name, " <br /> (").concat(toUnit(file.size), ")")).show();
          $('.wpstg--drag').hide();
          $('.wpstg--drag-or-upload').show();

          if (upload) {
            $('.wpstg--modal--actions .swal2-confirm').prop('disabled', true);
            that.snapshots.upload.start();
          }
        },
        baseDirectory: null,
        data: {
          file: null,
          search: [],
          replace: []
        }
      }
    },
    messages: {
      WARNING: 'warning',
      ERROR: 'error',
      INFO: 'info',
      DEBUG: 'debug',
      CRITICAL: 'critical',
      data: {
        all: [],
        // TODO RPoC
        info: [],
        error: [],
        critical: [],
        warning: [],
        debug: []
      },
      shouldWarn: function shouldWarn() {
        return that.snapshots.messages.data.error.length > 0 || that.snapshots.messages.data.critical.length > 0;
      },
      countByType: function countByType() {
        var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : that.snapshots.messages.ERROR;
        return that.snapshots.messages.data[type].length;
      },
      addMessage: function addMessage(message) {
        if (Array.isArray(message)) {
          message.forEach(function (item) {
            that.snapshots.messages.addMessage(item);
          });
          return;
        }

        var type = message.type.toLowerCase() || 'info';

        if (!that.snapshots.messages.data[type]) {
          that.snapshots.messages.data[type] = [];
        }

        that.snapshots.messages.data.all.push(message); // TODO RPoC

        that.snapshots.messages.data[type].push(message);
      },
      reset: function reset() {
        that.snapshots.messages.data = {
          all: [],
          info: [],
          error: [],
          critical: [],
          warning: [],
          debug: []
        };
      }
    },
    timer: {
      totalSeconds: 0,
      interval: null,
      start: function start() {
        if (null !== that.snapshots.timer.interval) {
          return;
        }

        var prettify = function prettify(seconds) {
          console.log("Process running for ".concat(seconds, " seconds")); // If potentially anything can exceed 24h execution time than that;
          // const _seconds = parseInt(seconds, 10)
          // const hours = Math.floor(_seconds / 3600)
          // const minutes = Math.floor(_seconds / 60) % 60
          // seconds = _seconds % 60
          //
          // return [hours, minutes, seconds]
          //   .map(v => v < 10 ? '0' + v : v)
          //   .filter((v,i) => v !== '00' || i > 0)
          //   .join(':')
          // ;
          // Are we sure we won't create anything that exceeds 24h execution time? If not then this;

          return "".concat(new Date(seconds * 1000).toISOString().substr(11, 8));
        };

        that.snapshots.timer.interval = setInterval(function () {
          $('.wpstg--modal--process--elapsed-time').text(prettify(that.snapshots.timer.totalSeconds));
          that.snapshots.timer.totalSeconds++;
        }, 1000);
      },
      stop: function stop() {
        that.snapshots.timer.totalSeconds = 0;

        if (that.snapshots.timer.interval) {
          clearInterval(that.snapshots.timer.interval);
          that.snapshots.timer.interval = null;
        }
      }
    },
    upload: {
      reader: null,
      file: null,
      iop: 1000 * 1024,
      uploadInfo: function uploadInfo(isShow) {
        var $containerUpload = $('.wpstg--modal--import--upload--process');
        var $containerUploader = $('.wpstg--uploader');

        if (isShow) {
          $containerUpload.css('display', 'flex');
          $containerUploader.hide();
          return;
        }

        $containerUploader.css('display', 'flex');
        $containerUpload.hide();
      },
      start: function start() {
        console.log("file ".concat(that.snapshots.modal["import"].data.file));
        that.snapshots.upload.reader = new FileReader();
        that.snapshots.upload.file = that.snapshots.modal["import"].file;
        that.snapshots.upload.uploadInfo(true);
        that.snapshots.upload.sendChunk();
      },
      sendChunk: function sendChunk() {
        var startsAt = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;

        if (!that.snapshots.upload.file) {
          return;
        }

        var isReset = startsAt < 1;
        var endsAt = startsAt + that.snapshots.upload.iop + 1;
        var blob = that.snapshots.upload.file.slice(startsAt, endsAt);

        that.snapshots.upload.reader.onloadend = function (event) {
          if (event.target.readyState !== FileReader.DONE) {
            return;
          }

          var body = new FormData();
          body.append('accessToken', wpstg.accessToken);
          body.append('nonce', wpstg.nonce);
          body.append('data', event.target.result);
          body.append('filename', that.snapshots.upload.file.name);
          body.append('reset', isReset ? '1' : '0');
          fetch("".concat(ajaxurl, "?action=wpstg--snapshots--import--file-upload"), {
            method: 'POST',
            body: body
          }).then(handleFetchErrors).then(function (res) {
            return res.json();
          }).then(function (res) {
            showAjaxFatalError(res, '', 'Submit an error report.');
            var writtenBytes = startsAt + that.snapshots.upload.iop;
            var percent = Math.floor(writtenBytes / that.snapshots.upload.file.size * 100);

            if (endsAt >= that.snapshots.upload.file.size) {
              that.snapshots.upload.uploadInfo(false);
              isLoading(false);
              $('.wpstg--modal--actions .swal2-confirm').prop('disabled', false);
              return;
            }

            $('.wpstg--modal--import--upload--progress--title > span').text(percent);
            $('.wpstg--modal--import--upload--progress').css('width', "".concat(percent, "%"));
            that.snapshots.upload.sendChunk(endsAt);
          })["catch"](function (e) {
            return showAjaxFatalError(e, '', 'Submit an error report.');
          });
        };

        that.snapshots.upload.reader.readAsDataURL(blob);
      }
    },
    status: {
      hasResponse: null,
      reTryAfter: 5000
    },
    init: function init() {
      this.create();
      this["delete"]();
      this.restore();
      this.edit(); // noinspection JSIgnoredPromiseFromCall

      that.snapshots.fetchListing();
      $('body').off('change', '#wpstg--snapshots--filter').on('change', '#wpstg--snapshots--filter', function () {
        var $records = $('#wpstg-existing-snapshots').find('> div[id][data-type].wpstg-snapshot');

        if (this.value === '') {
          $records.show();
        } else if (this.value === 'database') {
          $records.filter('[data-type="site"]').hide();
          $records.filter('[data-type="database"]').show();
        } else if (this.value === 'site') {
          $records.filter('[data-type="database"]').hide();
          $records.filter('[data-type="site"]').show();
        }
      }).on('click', '.wpstg--snapshot--download', function () {
        var url = this.getAttribute('data-url');

        if (url.length > 0) {
          window.location.href = url;
          return;
        }

        that.snapshots.downloadModal({
          titleExport: this.getAttribute('data-title-export'),
          title: this.getAttribute('data-title'),
          id: this.getAttribute('data-id'),
          btnTxtCancel: this.getAttribute('data-btn-cancel-txt'),
          btnTxtConfirm: this.getAttribute('data-btn-download-txt')
        });
      }).off('click', '#wpstg-import-snapshot').on('click', '#wpstg-import-snapshot', function () {
        that.snapshots.importModal();
      }) // Import
      .off('click', '.wpstg--snapshot--import--choose-option').on('click', '.wpstg--snapshot--import--choose-option', function () {
        var $this = $(this);
        var $parent = $this.parent();

        if (!$parent.hasClass('wpstg--show-options')) {
          $parent.addClass('wpstg--show-options');
          $this.text($this.attr('data-txtChoose'));
        } else {
          $parent.removeClass('wpstg--show-options');
          $this.text($this.attr('data-txtOther'));
        }
      }).off('click', '.wpstg--modal--snapshot--import--search-replace--new').on('click', '.wpstg--modal--snapshot--import--search-replace--new', function (e) {
        e.preventDefault();
        var $container = $(Swal.getContainer()).find('.wpstg--modal--snapshot--import--search-replace--input--container');
        var total = $container.find('.wpstg--modal--snapshot--import--search-replace--input-group').length;
        $container.append(that.snapshots.modal["import"].searchReplaceForm.replace(/{i}/g, total));
      }).off('input', '.wpstg--snapshot--import--search').on('input', '.wpstg--snapshot--import--search', function () {
        var index = parseInt(this.getAttribute('data-index'));

        if (!isNaN(index)) {
          that.snapshots.modal["import"].data.search[index] = this.value;
        }
      }).off('input', '.wpstg--snapshot--import--replace').on('input', '.wpstg--snapshot--import--replace', function () {
        var index = parseInt(this.getAttribute('data-index'));

        if (!isNaN(index)) {
          that.snapshots.modal["import"].data.replace[index] = this.value;
        }
      }) // Other Options
      .off('click', '.wpstg--snapshot--import--option[data-option]').on('click', '.wpstg--snapshot--import--option[data-option]', function () {
        var option = this.getAttribute('data-option');

        if (option === 'file') {
          $('input[type="file"][name="wpstg--snapshot--import--upload--file"]').click();
          return;
        }

        if (option === 'upload') {
          that.snapshots.modal["import"].containerFilesystem.hide();
          that.snapshots.modal["import"].containerUpload.show();
          $('.wpstg--snapshot--import--choose-option').click();
          $('.wpstg--modal--snapshot--import--search-replace--wrapper').show();
        }

        if (option !== 'filesystem') {
          return;
        }

        that.snapshots.modal["import"].containerUpload.hide();
        var $containerFilesystem = that.snapshots.modal["import"].containerFilesystem;
        $containerFilesystem.show();
        fetch("".concat(ajaxurl, "?action=wpstg--snapshots--import--file-list&_=").concat(Math.random(), "&accessToken=").concat(wpstg.accessToken, "&nonce=").concat(wpstg.nonce)).then(handleFetchErrors).then(function (res) {
          return res.json();
        }).then(function (res) {
          var $ul = $('.wpstg--modal--snapshot--import--filesystem ul');
          $ul.empty();

          if (!res || isEmpty(res)) {
            $ul.append("<span id=\"wpstg--snapshots--import--file-list-empty\">No import file found! Upload an import file to the folder above.</span><br />");
            $('.wpstg--modal--snapshot--import--search-replace--wrapper').hide();
            return;
          }

          $ul.append("<span id=\"wpstg--snapshots--import--file-list\">Select file to import:</span><br />");
          res.forEach(function (file, index) {
            // var checked = (index === 0) ? 'checked' : '';
            $ul.append("<li><label><input name=\"snapshot_import_file\" type=\"radio\" value=\"".concat(file.fullPath, "\">").concat(file.name, " <br /> ").concat(file.size, "</label></li>"));
          }); // $('.wpstg--modal--actions .swal2-confirm').prop('disabled', false);

          return res;
        })["catch"](function (e) {
          return showAjaxFatalError(e, '', 'Submit an error report.');
        });
      }).off('change', 'input[type="file"][name="wpstg--snapshot--import--upload--file"]').on('change', 'input[type="file"][name="wpstg--snapshot--import--upload--file"]', function () {
        that.snapshots.modal["import"].setFile(this.files[0] || null);
        $('.wpstg--snapshot--import--choose-option').click();
      }).off('change', 'input[type="radio"][name="snapshot_import_file"]').on('change', 'input[type="radio"][name="snapshot_import_file"]', function () {
        $('.wpstg--modal--actions .swal2-confirm').prop('disabled', false);
        that.snapshots.modal["import"].data.file = this.value;
      }) // Drag & Drop
      .on('drag dragstart dragend dragover dragenter dragleave drop', '.wpstg--modal--snapshot--import--upload--container', function (e) {
        e.preventDefault();
        e.stopPropagation();
      }).on('dragover dragenter', '.wpstg--modal--snapshot--import--upload--container', function () {
        $(this).addClass('wpstg--has-dragover');
      }).on('dragleave dragend drop', '.wpstg--modal--snapshot--import--upload--container', function () {
        $(this).removeClass('wpstg--has-dragover');
      }).on('drop', '.wpstg--modal--snapshot--import--upload--container', function (e) {
        that.snapshots.modal["import"].setFile(e.originalEvent.dataTransfer.files[0] || null);
      });
    },
    fetchListing: function fetchListing() {
      var isResetErrors = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      isLoading(true);

      if (isResetErrors) {
        resetErrors();
      }

      return fetch("".concat(ajaxurl, "?action=wpstg--snapshots--listing&_=").concat(Math.random(), "&accessToken=").concat(wpstg.accessToken, "&nonce=").concat(wpstg.nonce)).then(handleFetchErrors).then(function (res) {
        return res.json();
      }).then(function (res) {
        showAjaxFatalError(res, '', 'Submit an error report.');
        cache.get('#wpstg--tab--snapshot').html(res);
        isLoading(false);
        return res;
      })["catch"](function (e) {
        return showAjaxFatalError(e, '', 'Submit an error report.');
      });
    },
    "delete": function _delete() {
      $('#wpstg--tab--snapshot').off('click', '.wpstg-delete-snapshot[data-id]').on('click', '.wpstg-delete-snapshot[data-id]', function (e) {
        e.preventDefault();
        resetErrors();
        isLoading(true);
        cache.get('#wpstg-existing-snapshots').hide();
        var id = this.getAttribute('data-id');
        that.ajax({
          action: 'wpstg--snapshots--delete--confirm',
          id: id,
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, function (response) {
          showAjaxFatalError(response, '', ' Please submit an error report by using the REPORT ISSUE button.');
          isLoading(false);
          cache.get('#wpstg-delete-confirmation').html(response);
        });
      }) // Delete final confirmation page
      .off('click', '#wpstg-delete-snapshot').on('click', '#wpstg-delete-snapshot', function (e) {
        e.preventDefault();
        resetErrors();
        isLoading(true);
        var id = this.getAttribute('data-id');
        that.ajax({
          action: 'wpstg--snapshots--delete',
          id: id,
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, function (response) {
          showAjaxFatalError(response, '', ' Please submit an error report by using the REPORT ISSUE button.'); // noinspection JSIgnoredPromiseFromCall

          that.snapshots.fetchListing();
          isLoading(false);
        });
      }).off('click', '#wpstg-cancel-snapshot-delete').on('click', '#wpstg-cancel-snapshot-delete', function (e) {
        e.preventDefault();
        isLoading(false); // noinspection JSIgnoredPromiseFromCall

        that.snapshots.fetchListing();
      }); // Force delete if snapshot tables do not exist
      // TODO This is bloated, no need extra ID, use existing one?

      $('#wpstg-error-wrapper').off('click', '#wpstg-snapshot-force-delete').on('click', '#wpstg-snapshot-force-delete', function (e) {
        e.preventDefault();
        resetErrors();
        isLoading(true);
        var id = this.getAttribute('data-id');

        if (!confirm('Do you want to delete this snapshot ' + id + ' from the listed snapshots?')) {
          isLoading(false);
          return false;
        }

        that.ajax({
          action: 'wpstg--snapshots--delete',
          id: id,
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce
        }, function (response) {
          showAjaxFatalError(response, '', ' Please submit an error report by using the REPORT ISSUE button.'); // noinspection JSIgnoredPromiseFromCall

          that.snapshots.fetchListing();
          isLoading(false);
        });
      });
    },
    create: function create() {
      var createSnapshot = function createSnapshot(data) {
        resetErrors();

        if (that.snapshots.isCancelled) {
          // Swal.close();
          return;
        }

        var reset = data['reset'];
        delete data['reset'];
        var requestData = Object.assign({}, data);
        var useResponseTitle = true;

        if (data.type === 'database') {
          that.snapshots.type = data.type; // Only send to back-end what BE is expecting to receive.
          // Prevent error: Trying to hydrate DTO with value that does not exist.

          delete requestData['includedDirectories'];
          delete requestData['wpContentDir'];
          delete requestData['availableDirectories'];
          delete requestData['wpStagingDir'];
          delete requestData['exportDatabase'];
          delete requestData['includeOtherFilesInWpContent'];
          requestData = that.snapshots.requestData('tasks.snapshot.database.create', _objectSpread(_objectSpread({}, requestData), {}, {
            type: 'manual'
          }));
        } else if (data.type === 'site') {
          that.snapshots.type = data.type; // Only send to back-end what BE is expecting to receive.
          // Prevent error: Trying to hydrate DTO with value that does not exist.

          delete requestData['type'];
          requestData = that.snapshots.requestData('jobs.snapshot.site.create', requestData);
          useResponseTitle = false;
          requestData.jobs.snapshot.site.create.directories = [data.wpContentDir];
          requestData.jobs.snapshot.site.create.excludedDirectories = data.availableDirectories.split('|').filter(function (item) {
            return !data.includedDirectories.includes(item);
          }).map(function (item) {
            return item;
          });
          requestData.jobs.snapshot.site.create.includeOtherFilesInWpContent = [data.includeOtherFilesInWpContent]; // Do not exclude the wp-content/uploads/wp-staging using regex by default
          // This folder is excluded by PHP without REGEX.
          // requestData.jobs.snapshot.site.create.excludedDirectories.push(`#${data.wpStagingDir}*#`);
          // delete requestData.jobs.snapshot.site.create.includedDirectories;

          delete requestData.jobs.snapshot.site.create.wpContentDir;
          delete requestData.jobs.snapshot.site.create.wpStagingDir;
          delete requestData.jobs.snapshot.site.create.availableDirectories;
        } else {
          that.snapshots.type = null;
          Swal.close();
          showError('Invalid Snapshot Type');
          return;
        }

        that.snapshots.timer.start();

        var statusStop = function statusStop() {
          console.log('Status: Stop');
          clearInterval(that.snapshots.processInfo.interval);
          that.snapshots.processInfo.interval = null;
        };

        var status = function status() {
          if (that.snapshots.processInfo.interval !== null) {
            return;
          }

          console.log('Status: Start');
          that.snapshots.processInfo.interval = setInterval(function () {
            if (true === that.snapshots.isCancelled) {
              statusStop();
              return;
            }

            if (that.snapshots.status.hasResponse === false) {
              return;
            }

            that.snapshots.status.hasResponse = false;
            fetch("".concat(ajaxurl, "?action=wpstg--snapshots--status&accessToken=").concat(wpstg.accessToken, "&nonce=").concat(wpstg.nonce)).then(function (res) {
              return res.json();
            }).then(function (res) {
              that.snapshots.status.hasResponse = true;

              if (typeof res === 'undefined') {
                statusStop();
              }

              if (that.snapshots.processInfo.title === res.currentStatusTitle) {
                return;
              }

              that.snapshots.processInfo.title = res.currentStatusTitle;
              var $container = $(Swal.getContainer());
              $container.find('.wpstg--modal--process--title').text(res.currentStatusTitle);
              $container.find('.wpstg--modal--process--percent').text('0');
            })["catch"](function (e) {
              that.snapshots.status.hasResponse = true;
              showAjaxFatalError(e, '', 'Submit an error report.');
            });
          }, 5000);
        };

        WPStaging.ajax({
          action: 'wpstg--snapshots--create',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          reset: reset,
          wpstg: requestData
        }, function (response) {
          if (typeof response === 'undefined') {
            setTimeout(function () {
              createSnapshot(data);
            }, wpstg.delayReq);
            return;
          }

          that.snapshots.processResponse(response, useResponseTitle);

          if (!useResponseTitle && !that.snapshots.processInfo.interval) {
            status();
          }

          if (response.status === false) {
            createSnapshot(data);
          } else if (response.status === true) {
            $('#wpstg--progress--status').text('Snapshot successfully created!');
            that.snapshots.type = null;

            if (that.snapshots.messages.shouldWarn()) {
              // noinspection JSIgnoredPromiseFromCall
              that.snapshots.fetchListing();
              that.snapshots.logsModal();
              return;
            }

            statusStop();
            Swal.close();
            that.snapshots.fetchListing().then(function () {
              if (!response.snapshotId) {
                showError('Failed to get snapshot ID from response');
                return;
              } // TODO RPoC


              var $el = $(".wpstg--snapshot--download[data-id=\"".concat(response.snapshotId, "\"]"));
              that.snapshots.downloadModal({
                id: $el.data('id'),
                url: $el.data('url'),
                title: $el.data('title'),
                titleExport: $el.data('title-export'),
                btnTxtCancel: $el.data('btn-cancel-txt'),
                btnTxtConfirm: $el.data('btn-download-txt')
              });
              $('.wpstg--modal--download--logs--wrapper').show();
              var $logsContainer = $('.wpstg--modal--process--logs');
              that.snapshots.messages.data.all.forEach(function (message) {
                var msgClass = "wpstg--modal--process--msg--".concat(message.type.toLowerCase());
                $logsContainer.append("<p class=\"".concat(msgClass, "\">[").concat(message.type, "] - [").concat(message.date, "] - ").concat(message.message, "</p>"));
              });
            });
          } else {
            setTimeout(function () {
              createSnapshot(data);
            }, wpstg.delayReq);
          }
        }, 'json', false, 0, // Don't retry upon failure
        1.25);
      };

      var $body = $('body');
      $body.off('click', 'input[name="snapshot_type"]').on('click', 'input[name="snapshot_type"]', function () {
        var $advancedOptions = $('.wpstg-advanced-options');

        if (this.value === 'database') {
          $advancedOptions.hide();
          return;
        }

        $advancedOptions.show();
      }).off('click', '.wpstg--tab--toggle').on('click', '.wpstg--tab--toggle', function () {
        var $this = $(this);
        var $target = $($this.attr('data-target'));
        $target.toggle();

        if ($target.is(':visible')) {
          $this.find('span').text('▼');
        } else {
          $this.find('span').text('►');
        }
      }).off('change', '[name="includedDirectories\[\]"], [type="checkbox"][name="export_database"]').on('change', '[type="checkbox"][name="includedDirectories\[\]"], [type="checkbox"][name="export_database"]', function () {
        var totalDirs = $('[type="checkbox"][name="includedDirectories\[\]"]:checked').length;
        var isExportDatabase = $('[type="checkbox"][name="export_database"]:checked').length === 1;

        if (totalDirs < 1 && !isExportDatabase) {
          $('.swal2-confirm').prop('disabled', true);
        } else {
          $('.swal2-confirm').prop('disabled', false);
        }
      }); // Add backup name and notes

      $('#wpstg--tab--snapshot').off('click', '#wpstg-new-snapshot').on('click', '#wpstg-new-snapshot', /*#__PURE__*/function () {
        var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(e) {
          var $newSnapshotModal, html, btnTxt, _yield$Swal$fire, formValues;

          return regeneratorRuntime.wrap(function _callee$(_context) {
            while (1) {
              switch (_context.prev = _context.next) {
                case 0:
                  resetErrors();
                  e.preventDefault();
                  that.snapshots.isCancelled = false;

                  if (!that.snapshots.modal.create.html || !that.snapshots.modal.create.confirmBtnTxt) {
                    $newSnapshotModal = $('#wpstg--modal--snapshot--new');
                    html = $newSnapshotModal.html();
                    btnTxt = $newSnapshotModal.attr('data-confirmButtonText');
                    that.snapshots.modal.create.html = html || null;
                    that.snapshots.modal.create.confirmBtnTxt = btnTxt || null;
                    $newSnapshotModal.remove();
                  }

                  _context.next = 6;
                  return Swal.fire({
                    title: '',
                    html: that.snapshots.modal.create.html,
                    focusConfirm: false,
                    confirmButtonText: that.snapshots.modal.create.confirmBtnTxt,
                    showCancelButton: true,
                    preConfirm: function preConfirm() {
                      var container = Swal.getContainer();

                      if (document.getElementById('snapshot_type_database').offsetParent == '') {
                        var snapshotType = 'database';
                      } else {
                        var snapshotType = container.querySelector('input[name="snapshot_type"]:checked').value;
                      }

                      return {
                        type: snapshotType || null,
                        name: container.querySelector('input[name="snapshot_name"]').value || null,
                        notes: container.querySelector('textarea[name="snapshot_note"]').value || null,
                        includedDirectories: Array.from(container.querySelectorAll('input[name="includedDirectories\\[\\]"]:checked') || []).map(function (i) {
                          return i.value;
                        }),
                        wpContentDir: container.querySelector('input[name="wpContentDir"]').value || null,
                        availableDirectories: container.querySelector('input[name="availableDirectories"]').value || null,
                        wpStagingDir: container.querySelector('input[name="wpStagingDir"]').value || null,
                        exportDatabase: container.querySelector('input[name="export_database"]:checked') !== null,
                        includeOtherFilesInWpContent: container.querySelector('input[name="includeOtherFilesInWpContent"]:checked') !== null
                      };
                    }
                  });

                case 6:
                  _yield$Swal$fire = _context.sent;
                  formValues = _yield$Swal$fire.value;

                  if (formValues) {
                    _context.next = 10;
                    break;
                  }

                  return _context.abrupt("return");

                case 10:
                  formValues.reset = true;
                  that.snapshots.process({
                    execute: function execute() {
                      that.snapshots.messages.reset();
                      createSnapshot(formValues);
                    }
                  });

                case 12:
                case "end":
                  return _context.stop();
              }
            }
          }, _callee);
        }));

        return function (_x) {
          return _ref.apply(this, arguments);
        };
      }());
    },
    restore: function restore() {
      var restoreSnapshot = function restoreSnapshot(prefix, reset) {
        isLoading(true);
        resetErrors();

        if (typeof reset === 'undefined') {
          reset = false;
        }

        WPStaging.ajax({
          action: 'wpstg--snapshots--restore',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          wpstg: {
            tasks: {
              snapshot: {
                database: {
                  create: {
                    source: prefix,
                    reset: reset
                  }
                }
              }
            }
          }
        }, function (response) {
          if (typeof response === 'undefined') {
            setTimeout(function () {
              restoreSnapshot(prefix);
            }, wpstg.delayReq);
            return;
          }

          that.snapshots.processResponse(response);

          if (response.status === false || response.job_done === false) {
            restoreSnapshot(prefix);
          } else if (response.status === true && response.job_done === true) {
            isLoading(false);
            $('.wpstg--modal--process--title').text('Snapshot successfully restored');
            setTimeout(function () {
              Swal.close(); // noinspection JSIgnoredPromiseFromCall

              that.snapshots.fetchListing();
            }, 1000);
          } else {
            setTimeout(function () {
              restoreSnapshot(prefix);
            }, wpstg.delayReq);
          }
        }, 'json', false, 0, 1.25);
      };

      $('#wpstg--tab--snapshot').off('click', '.wpstg--snapshot--restore[data-id]').on('click', '.wpstg--snapshot--restore[data-id]', function (e) {
        e.preventDefault();
        resetErrors();
        that.ajax({
          action: 'wpstg--snapshots--restore--confirm',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          id: $(this).data('id')
        }, function (data) {
          cache.get('#wpstg--tab--snapshot').html(data);
        });
      }).off('click', '#wpstg--snapshot--restore--cancel').on('click', '#wpstg--snapshot--restore--cancel', function (e) {
        resetErrors();
        e.preventDefault(); // noinspection JSIgnoredPromiseFromCall

        that.snapshots.fetchListing();
      }).off('click', '#wpstg--snapshot--restore[data-id]').on('click', '#wpstg--snapshot--restore[data-id]', function (e) {
        e.preventDefault();
        resetErrors();
        var id = this.getAttribute('data-id');
        that.snapshots.process({
          execute: function execute() {
            that.snapshots.messages.reset();
            restoreSnapshot(id, true);
          },
          isShowCancelButton: false
        });
      });
    },
    // Edit snapshots name and notes
    edit: function edit() {
      $('#wpstg--tab--snapshot').off('click', '.wpstg--snapshot--edit[data-id]').on('click', '.wpstg--snapshot--edit[data-id]', /*#__PURE__*/function () {
        var _ref2 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(e) {
          var $this, name, notes, _yield$Swal$fire2, formValues;

          return regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) {
              switch (_context2.prev = _context2.next) {
                case 0:
                  e.preventDefault();
                  $this = $(this);
                  name = $this.data('name');
                  notes = $this.data('notes');
                  _context2.next = 6;
                  return Swal.fire({
                    title: '',
                    html: "\n                    <label id=\"wpstg-snapshot-edit-name\">Backup Name</label>\n                    <input id=\"wpstg-snapshot-edit-name-input\" class=\"swal2-input\" value=\"".concat(name, "\">\n                    <label>Additional Notes</label>\n                    <textarea id=\"wpstg-snapshot-edit-notes-textarea\" class=\"swal2-textarea\">").concat(notes, "</textarea>\n                  "),
                    focusConfirm: false,
                    confirmButtonText: 'Update Backup',
                    showCancelButton: true,
                    preConfirm: function preConfirm() {
                      return {
                        name: document.getElementById('wpstg-snapshot-edit-name-input').value || null,
                        notes: document.getElementById('wpstg-snapshot-edit-notes-textarea').value || null
                      };
                    }
                  });

                case 6:
                  _yield$Swal$fire2 = _context2.sent;
                  formValues = _yield$Swal$fire2.value;

                  if (formValues) {
                    _context2.next = 10;
                    break;
                  }

                  return _context2.abrupt("return");

                case 10:
                  that.ajax({
                    action: 'wpstg--snapshots--edit',
                    accessToken: wpstg.accessToken,
                    nonce: wpstg.nonce,
                    id: $this.data('id'),
                    name: formValues.name,
                    notes: formValues.notes
                  }, function (response) {
                    showAjaxFatalError(response, '', 'Submit an error report.'); // noinspection JSIgnoredPromiseFromCall

                    that.snapshots.fetchListing();
                  });

                case 11:
                case "end":
                  return _context2.stop();
              }
            }
          }, _callee2, this);
        }));

        return function (_x2) {
          return _ref2.apply(this, arguments);
        };
      }());
    },
    cancel: function cancel() {
      that.snapshots.timer.stop();
      that.snapshots.isCancelled = true;
      Swal.close();
      setTimeout(function () {
        return that.ajax({
          action: 'wpstg--snapshots--cancel',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          type: that.snapshots.type
        }, function (response) {
          showAjaxFatalError(response, '', 'Submit an error report.');
        });
      }, 500);
    },

    /**
         * If process.execute exists, process.data and process.onResponse is not used
         * process = { data: {}, onResponse: (resp) => {}, onAfterClose: () => {}, execute: () => {}, isShowCancelButton: bool }
         * @param {object} process
         */
    process: function process(_process) {
      if (typeof _process.execute !== 'function' && (!_process.data || !_process.onResponse)) {
        Swal.close();
        showError('process.data and / or process.onResponse is not set');
        return;
      } // TODO move to backend and get the contents as xhr response?


      if (!that.snapshots.modal.process.html || !that.snapshots.modal.process.cancelBtnTxt) {
        var $modal = $('#wpstg--modal--snapshot--process');
        var html = $modal.html();
        var btnTxt = $modal.attr('data-cancelButtonText');
        that.snapshots.modal.process.html = html || null;
        that.snapshots.modal.process.cancelBtnTxt = btnTxt || null;
        $modal.remove();
      }

      $('body').off('click', '.wpstg--modal--process--logs--tail').on('click', '.wpstg--modal--process--logs--tail', function (e) {
        e.preventDefault();
        var container = Swal.getContainer();
        var $logs = $(container).find('.wpstg--modal--process--logs');
        $logs.toggle();

        if ($logs.is(':visible')) {
          container.childNodes[0].style.width = '100%';
          container.style['z-index'] = 9999;
        } else {
          container.childNodes[0].style.width = '600px';
        }
      });
      _process.isShowCancelButton = false !== _process.isShowCancelButton;
      that.snapshots.modal.process.modal = Swal.mixin({
        customClass: {
          cancelButton: 'wpstg--btn--cancel wpstg-blue-primary wpstg-link-btn',
          content: 'wpstg--process--content'
        },
        buttonsStyling: false
      }).fire({
        html: that.snapshots.modal.process.html,
        cancelButtonText: that.snapshots.modal.process.cancelBtnTxt,
        showCancelButton: _process.isShowCancelButton,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        width: 600,
        onRender: function onRender() {
          var _btnCancel = Swal.getContainer().getElementsByClassName('swal2-cancel wpstg--btn--cancel')[0];

          var btnCancel = _btnCancel.cloneNode(true);

          _btnCancel.parentNode.replaceChild(btnCancel, _btnCancel);

          btnCancel.addEventListener('click', function (e) {
            if (confirm('Are You Sure? This will cancel the process!')) {
              Swal.close();
            }
          });

          if (typeof _process.execute === 'function') {
            _process.execute();

            return;
          }

          if (!_process.data || !_process.onResponse) {
            Swal.close();
            showError('process.data and / or process.onResponse is not set');
            return;
          }

          that.ajax(_process.data, _process.onResponse);
        },
        onAfterClose: function onAfterClose() {
          return typeof _process.onAfterClose === 'function' && _process.onAfterClose();
        },
        onClose: function onClose() {
          console.log('cancelled');
          that.snapshots.cancel();
        }
      });
    },
    processResponse: function processResponse(response, useTitle) {
      if (response === null) {
        Swal.close();
        showError('Invalid Response; null');
        throw new Error("Invalid Response; ".concat(response));
      }

      var $container = $(Swal.getContainer());

      var title = function title() {
        if ((response.title || response.statusTitle) && useTitle === true) {
          $container.find('.wpstg--modal--process--title').text(response.title || response.statusTitle);
        }
      };

      var percentage = function percentage() {
        if (response.percentage) {
          $container.find('.wpstg--modal--process--percent').text(response.percentage);
        }
      };

      var logs = function logs() {
        if (!response.messages) {
          return;
        }

        var $logsContainer = $container.find('.wpstg--modal--process--logs');
        var stoppingTypes = [that.snapshots.messages.ERROR, that.snapshots.messages.CRITICAL];

        var appendMessage = function appendMessage(message) {
          if (Array.isArray(message)) {
            var _iterator = _createForOfIteratorHelper(message),
                _step;

            try {
              for (_iterator.s(); !(_step = _iterator.n()).done;) {
                var item = _step.value;
                appendMessage(item);
              }
            } catch (err) {
              _iterator.e(err);
            } finally {
              _iterator.f();
            }

            return;
          }

          var msgClass = "wpstg--modal--process--msg--".concat(message.type.toLowerCase());
          $logsContainer.append("<p class=\"".concat(msgClass, "\">[").concat(message.type, "] - [").concat(message.date, "] - ").concat(message.message, "</p>"));

          if (stoppingTypes.includes(message.type.toLowerCase())) {
            that.snapshots.cancel();
            setTimeout(that.snapshots.logsModal, 500);
          }
        };

        var _iterator2 = _createForOfIteratorHelper(response.messages),
            _step2;

        try {
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var message = _step2.value;

            if (!message) {
              continue;
            }

            that.snapshots.messages.addMessage(message);
            appendMessage(message);
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }

        if ($logsContainer.is(':visible')) {
          $logsContainer.scrollTop($logsContainer[0].scrollHeight);
        }

        if (!that.snapshots.messages.shouldWarn()) {
          return;
        }

        var $btnShowLogs = $container.find('.wpstg--modal--process--logs--tail');
        $btnShowLogs.html($btnShowLogs.attr('data-txt-bad'));
        $btnShowLogs.find('.wpstg--modal--logs--critical-count').text(that.snapshots.messages.countByType(that.snapshots.messages.CRITICAL));
        $btnShowLogs.find('.wpstg--modal--logs--error-count').text(that.snapshots.messages.countByType(that.snapshots.messages.ERROR));
        $btnShowLogs.find('.wpstg--modal--logs--warning-count').text(that.snapshots.messages.countByType(that.snapshots.messages.WARNING));
      };

      title();
      percentage();
      logs();

      if (response.status === true && response.job_done === true) {
        that.snapshots.timer.stop();
        that.snapshots.isCancelled = true;
      }
    },
    requestData: function requestData(notation, data) {
      var obj = {};
      var keys = notation.split('.');
      var lastIndex = keys.length - 1;
      keys.reduce(function (accumulated, current, index) {
        return accumulated[current] = index >= lastIndex ? data : {};
      }, obj);
      return obj;
    },
    logsModal: function logsModal() {
      Swal.fire({
        html: "<div class=\"wpstg--modal--error--logs\" style=\"display:block\"></div><div class=\"wpstg--modal--process--logs\" style=\"display:block\"></div>",
        width: '95%',
        onRender: function onRender() {
          var $container = $(Swal.getContainer());
          $container[0].style['z-index'] = 9999;
          var $logsContainer = $container.find('.wpstg--modal--process--logs');
          var $errorContainer = $container.find('.wpstg--modal--error--logs');
          var $translations = $('#wpstg--js--translations');
          var messages = that.snapshots.messages;
          var title = $translations.attr('data-modal-logs-title').replace('{critical}', messages.countByType(messages.CRITICAL)).replace('{errors}', messages.countByType(messages.ERROR)).replace('{warnings}', messages.countByType(messages.WARNING));
          $errorContainer.before("<h3>".concat(title, "</h3>"));
          var warnings = [that.snapshots.messages.CRITICAL, that.snapshots.messages.ERROR, that.snapshots.messages.WARNING];

          if (!that.snapshots.messages.shouldWarn()) {
            $errorContainer.hide();
          }

          var _iterator3 = _createForOfIteratorHelper(messages.data.all),
              _step3;

          try {
            for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
              var message = _step3.value;
              var msgClass = "wpstg--modal--process--msg--".concat(message.type.toLowerCase()); // TODO RPoC

              if (warnings.includes(message.type)) {
                $errorContainer.append("<p class=\"".concat(msgClass, "\">[").concat(message.type, "] - [").concat(message.date, "] - ").concat(message.message, "</p>"));
              }

              $logsContainer.append("<p class=\"".concat(msgClass, "\">[").concat(message.type, "] - [").concat(message.date, "] - ").concat(message.message, "</p>"));
            }
          } catch (err) {
            _iterator3.e(err);
          } finally {
            _iterator3.f();
          }
        },
        onOpen: function onOpen(container) {
          var $logsContainer = $(container).find('.wpstg--modal--process--logs');
          $logsContainer.scrollTop($logsContainer[0].scrollHeight);
        }
      });
    },
    downloadModal: function downloadModal(_ref3) {
      var _ref3$title = _ref3.title,
          title = _ref3$title === void 0 ? null : _ref3$title,
          _ref3$titleExport = _ref3.titleExport,
          titleExport = _ref3$titleExport === void 0 ? null : _ref3$titleExport,
          _ref3$id = _ref3.id,
          id = _ref3$id === void 0 ? null : _ref3$id,
          _ref3$url = _ref3.url,
          url = _ref3$url === void 0 ? null : _ref3$url,
          _ref3$btnTxtCancel = _ref3.btnTxtCancel,
          btnTxtCancel = _ref3$btnTxtCancel === void 0 ? 'Cancel' : _ref3$btnTxtCancel,
          _ref3$btnTxtConfirm = _ref3.btnTxtConfirm,
          btnTxtConfirm = _ref3$btnTxtConfirm === void 0 ? 'Download' : _ref3$btnTxtConfirm;

      if (null === that.snapshots.modal.download.html) {
        var $el = $('#wpstg--modal--snapshot--download');
        that.snapshots.modal.download.html = $el.html();
        $el.remove();
      }

      var exportModal = function exportModal() {
        return Swal.fire({
          html: "<h2>".concat(titleExport, "</h2><span class=\"wpstg-loader\"></span>"),
          showCancelButton: false,
          showConfirmButton: false,
          onRender: function onRender() {
            that.ajax({
              action: 'wpstg--snapshots--export',
              accessToken: wpstg.accessToken,
              nonce: wpstg.nonce,
              id: id
            }, function (response) {
              if (!response || !response.success || !response.data || response.data.length < 1) {
                return;
              }

              var a = document.createElement('a');
              a.style.display = 'none';
              a.href = response.data;
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              Swal.close();
            });
          }
        });
      };

      Swal.mixin({
        customClass: {
          cancelButton: 'wpstg--btn--cancel wpstg-blue-primary wpstg-link-btn',
          confirmButton: 'wpstg--btn--confirm wpstg-blue-primary wpstg-button wpstg-link-btn',
          actions: 'wpstg--modal--actions'
        },
        buttonsStyling: false
      }).fire({
        icon: 'success',
        html: that.snapshots.modal.download.html.replace('{title}', title).replace('{btnTxtLog}', 'Show Logs'),
        cancelButtonText: btnTxtCancel,
        confirmButtonText: btnTxtConfirm,
        showCancelButton: true,
        showConfirmButton: true
      }).then(function (isConfirm) {
        if (!isConfirm || !isConfirm.value) {
          return;
        }

        if (url && url.length > 0) {
          window.location.href = url;
          return;
        }

        exportModal();
      });
    },
    importModal: function importModal() {
      var restoreSiteSnapshot = function restoreSiteSnapshot(data) {
        resetErrors();

        if (that.snapshots.isCancelled) {
          console.log('cancelled'); // Swal.close();

          return;
        }

        var reset = data['reset'];
        delete data['reset'];
        data['mergeMediaFiles'] = 1; // always merge for uploads / media

        var requestData = Object.assign({}, data);
        requestData = that.snapshots.requestData('jobs.snapshot.site.restore', _objectSpread({}, that.snapshots.modal["import"].data));
        that.snapshots.timer.start();

        var statusStop = function statusStop() {
          console.log('Status: Stop');
          clearInterval(that.snapshots.processInfo.interval);
          that.snapshots.processInfo.interval = null;
        };

        var status = function status() {
          if (that.snapshots.processInfo.interval !== null) {
            return;
          }

          console.log('Status: Start');
          that.snapshots.processInfo.interval = setInterval(function () {
            if (true === that.snapshots.isCancelled) {
              statusStop();
              return;
            }

            if (that.snapshots.status.hasResponse === false) {
              return;
            }

            that.snapshots.status.hasResponse = false;
            fetch("".concat(ajaxurl, "?action=wpstg--snapshots--status&process=restore&accessToken=").concat(wpstg.accessToken, "&nonce=").concat(wpstg.nonce)).then(function (res) {
              return res.json();
            }).then(function (res) {
              that.snapshots.status.hasResponse = true;

              if (typeof res === 'undefined') {
                statusStop();
              }

              if (that.snapshots.processInfo.title === res.currentStatusTitle) {
                return;
              }

              that.snapshots.processInfo.title = res.currentStatusTitle;
              var $container = $(Swal.getContainer());
              $container.find('.wpstg--modal--process--title').text(res.currentStatusTitle);
              $container.find('.wpstg--modal--process--percent').text('0');
            })["catch"](function (e) {
              that.snapshots.status.hasResponse = true;
              showAjaxFatalError(e, '', 'Submit an error report.');
            });
          }, 5000);
        };

        WPStaging.ajax({
          action: 'wpstg--snapshots--site--restore',
          accessToken: wpstg.accessToken,
          nonce: wpstg.nonce,
          reset: reset,
          wpstg: requestData
        }, function (response) {
          if (typeof response === 'undefined') {
            setTimeout(function () {
              restoreSiteSnapshot(data);
            }, wpstg.delayReq);
            return;
          }

          that.snapshots.processResponse(response, true);

          if (!that.snapshots.processInfo.interval) {
            status();
          }

          if (response.status === false) {
            restoreSiteSnapshot(data);
          } else if (response.status === true) {
            $('#wpstg--progress--status').text('Snapshot successfully restored!');
            that.snapshots.type = null;

            if (that.snapshots.messages.shouldWarn()) {
              // noinspection JSIgnoredPromiseFromCall
              that.snapshots.fetchListing();
              that.snapshots.logsModal();
              return;
            }

            statusStop();
            var logEntries = $('.wpstg--modal--process--logs').get(1).innerHTML;
            var html = '<div class="wpstg--modal--process--logs">' + logEntries + '</div>';
            var issueFound = html.includes('wpstg--modal--process--msg--warning') || html.includes('wpstg--modal--process--msg--error') ? 'Issues(s) found! ' : '';
            console.log('errors found: ' + issueFound); // var errorMessage = html.includes('wpstg--modal--process--msg--error') ? 'Errors(s) found! ' : '';
            // var Message = warningMessage + errorMessage;
            // Swal.close();

            Swal.fire({
              icon: 'success',
              title: 'Finished',
              html: 'System restored from snapshot. <br/><span class="wpstg--modal--process--msg-found">' + issueFound + '</span><button class="wpstg--modal--process--logs--tail" data-txt-bad="">Show Logs</button><br/>' + html
            }); // noinspection JSIgnoredPromiseFromCall

            that.snapshots.fetchListing();
          } else {
            setTimeout(function () {
              restoreSiteSnapshot(data);
            }, wpstg.delayReq);
          }
        }, 'json', false, 0, // Don't retry upon failure
        1.25);
      };

      if (!that.snapshots.modal["import"].html) {
        var $modal = $('#wpstg--modal--snapshot--import'); // Search & Replace Form

        var $form = $modal.find('.wpstg--modal--snapshot--import--search-replace--input--container');
        that.snapshots.modal["import"].searchReplaceForm = $form.html();
        $form.find('.wpstg--modal--snapshot--import--search-replace--input-group').remove();
        $form.html(that.snapshots.modal["import"].searchReplaceForm.replace(/{i}/g, 0));
        that.snapshots.modal["import"].html = $modal.html();
        that.snapshots.modal["import"].baseDirectory = $modal.attr('data-baseDirectory');
        that.snapshots.modal["import"].btnTxtNext = $modal.attr('data-nextButtonText');
        that.snapshots.modal["import"].btnTxtConfirm = $modal.attr('data-confirmButtonText');
        that.snapshots.modal["import"].btnTxtCancel = $modal.attr('data-cancelButtonText');
        $modal.remove();
      }

      that.snapshots.modal["import"].data.search = [];
      that.snapshots.modal["import"].data.replace = [];
      var $btnConfirm = null;
      Swal.mixin({
        customClass: {
          confirmButton: 'wpstg--btn--confirm wpstg-blue-primary wpstg-button wpstg-link-btn',
          cancelButton: 'wpstg--btn--cancel wpstg-blue-primary wpstg-link-btn',
          actions: 'wpstg--modal--actions'
        },
        buttonsStyling: false // progressSteps: ['1', '2']

      }).queue([{
        html: that.snapshots.modal["import"].html,
        confirmButtonText: that.snapshots.modal["import"].btnTxtNext,
        showCancelButton: false,
        showConfirmButton: true,
        showLoaderOnConfirm: true,
        width: 650,
        onRender: function onRender() {
          $btnConfirm = $('.wpstg--modal--actions .swal2-confirm');
          $btnConfirm.prop('disabled', true);
          that.snapshots.modal["import"].containerUpload = $('.wpstg--modal--snapshot--import--upload');
          that.snapshots.modal["import"].containerFilesystem = $('.wpstg--modal--snapshot--import--filesystem');
        },
        preConfirm: function preConfirm() {
          var body = new FormData();
          body.append('accessToken', wpstg.accessToken);
          body.append('nonce', wpstg.nonce);
          body.append('filePath', that.snapshots.modal["import"].data.file);
          that.snapshots.modal["import"].data.search.forEach(function (item, index) {
            body.append("search[".concat(index, "]"), item);
          });
          that.snapshots.modal["import"].data.replace.forEach(function (item, index) {
            body.append("replace[".concat(index, "]"), item);
          });
          return fetch("".concat(ajaxurl, "?action=wpstg--snapshots--import--file-info"), {
            method: 'POST',
            body: body
          }).then(handleFetchErrors).then(function (res) {
            return res.json();
          }).then(function (html) {
            return Swal.insertQueueStep({
              html: html,
              confirmButtonText: that.snapshots.modal["import"].btnTxtConfirm,
              cancelButtonText: that.snapshots.modal["import"].btnTxtCancel,
              showCancelButton: true
            });
          })["catch"](function (e) {
            return showAjaxFatalError(e, '', 'Submit an error report.');
          });
        }
      }]).then(function (res) {
        if (!res || !res.value || !res.value[1] || res.value[1] !== true) {
          return;
        }

        that.snapshots.isCancelled = false;
        var data = that.snapshots.modal["import"].data;
        data['file'] = that.snapshots.modal["import"].baseDirectory + data['file'];
        data['reset'] = true;
        that.snapshots.process({
          execute: function execute() {
            that.snapshots.messages.reset();
            restoreSiteSnapshot(data);
          }
        });
      });
    }
  };
  return that;
}(jQuery);

jQuery(document).ready(function () {
  WPStaging.init();
});
/**
 * Report Issue modal
 */

jQuery(document).ready(function ($) {
  $('#wpstg-report-issue-button').click(function (e) {
    $('.wpstg-report-issue-form').toggleClass('wpstg-report-show');
    e.preventDefault();
  });
  $('body').on('click', '#wpstg-snapshots-report-issue-button', function (e) {
    $('.wpstg-report-issue-form').toggleClass('wpstg-report-show');
    console.log('test');
    e.preventDefault();
  });
  $('#wpstg-report-cancel').click(function (e) {
    $('.wpstg-report-issue-form').removeClass('wpstg-report-show');
    e.preventDefault();
  });
  /*
     * Close Success Modal
     */

  $('body').on('click', '#wpstg-success-button', function (e) {
    e.preventDefault();
    $('.wpstg-report-issue-form').removeClass('wpstg-report-show');
  });

  function sendIssueReport(button) {
    var forceSend = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'false';
    var spinner = button.next();
    var email = $('.wpstg-report-email').val();
    var hosting_provider = $('.wpstg-report-hosting-provider').val();
    var message = $('.wpstg-report-description').val();
    var syslog = $('.wpstg-report-syslog').is(':checked');
    var terms = $('.wpstg-report-terms').is(':checked');
    button.attr('disabled', true);
    spinner.css('visibility', 'visible');
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      async: true,
      data: {
        'action': 'wpstg_send_report',
        'accessToken': wpstg.accessToken,
        'nonce': wpstg.nonce,
        'wpstg_email': email,
        'wpstg_provider': hosting_provider,
        'wpstg_message': message,
        'wpstg_syslog': +syslog,
        'wpstg_terms': +terms,
        'wpstg_force_send': forceSend
      }
    }).done(function (data) {
      button.attr('disabled', false);
      spinner.css('visibility', 'hidden');

      if (data.errors.length > 0) {
        $('.wpstg-report-issue-form .wpstg-message').remove();
        var errorMessage = $('<div />').addClass('wpstg-message wpstg-error-message');
        $.each(data.errors, function (key, value) {
          if (value.status === 'already_submitted') {
            errorMessage = '';
            Swal.fire({
              title: '',
              customClass: {
                container: 'wpstg-issue-resubmit-confirmation'
              },
              icon: 'warning',
              html: value.message,
              showCloseButton: true,
              showCancelButton: true,
              focusConfirm: false,
              confirmButtonText: 'Yes',
              cancelButtonText: 'No'
            }).then(function (result) {
              if (result.isConfirmed) {
                sendIssueReport(button, 'true');
              }
            });
          } else {
            errorMessage.append('<p>' + value + '</p>');
          }
        });
        $('.wpstg-report-issue-form').prepend(errorMessage);
      } else {
        var successMessage = $('<div />').addClass('wpstg-message wpstg-success-message');
        successMessage.append('<p>Thanks for submitting your request! You should receive an auto reply mail with your ticket ID immediately for confirmation!<br><br>If you do not get that mail please contact us directly at <strong>support@wp-staging.com</strong></p>');
        $('.wpstg-report-issue-form').html(successMessage);
        $('.wpstg-success-message').append('<div style="float:right;margin-top:10px;"><a id="wpstg-success-button" href="#">Close</a></div>'); // Hide message

        setTimeout(function () {
          $('.wpstg-report-issue-form').removeClass('wpstg-report-active');
        }, 2000);
      }
    });
  }

  $('#wpstg-report-submit').click(function (e) {
    var self = $(this);
    sendIssueReport(self, 'false');
    e.preventDefault();
  }); // Open/close actions drop down menu

  $(document).on('click', '.wpstg-dropdown>.wpstg-dropdown-toggler', function (e) {
    e.preventDefault();
    $(e.target).next('.wpstg-dropdown-menu').toggleClass('shown');
  }); // Close action drop down menu if clicked anywhere outside

  document.addEventListener('click', function (event) {
    var isClickInside = event.target.closest('.wpstg-dropdown-toggler');

    if (!isClickInside) {
      var dropDown = document.getElementsByClassName('wpstg-dropdown-menu');

      for (var i = 0; i < dropDown.length; i++) {
        dropDown[i].classList.remove('shown');
      }
    }
  });
});