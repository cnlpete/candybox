/* Quote comment */
function quote(sName, sDivId) {
  var oTextField  = $('#js-create_commment_text');
  var sQuote      = $('#' + sDivId).html();
  var sNewMessage = "[quote=" + sName + "]" + sQuote + "[/quote]\n";
  oTextField.val(oTextField.val() + sNewMessage);

  return false;
}

/**
 *
 *
 *
 */
function stripNoAlphaChars(sValue) {
  sValue = sValue.replace(/ /g, "_");
  sValue = sValue.replace(/Ä/g, "Ae");
  sValue = sValue.replace(/ä/g, "ae");
  sValue = sValue.replace(/Ö/g, "Oe");
  sValue = sValue.replace(/ö/g, "oe");
  sValue = sValue.replace(/Ü/g, "Ue");
  sValue = sValue.replace(/ü/g, "ue");
  sValue = sValue.replace(/ß/g, "ss");
  sValue = sValue.replace(/\W/g, "_");
  return sValue;
}

/**
 * Confirm that user really wants to destroy an entry.
 *
 * @param string sUrl URL to delete data from
 * @param string sDivId DIV to animate. If sDivId is set this will be an AJAX call.
 *
 */
function confirmDestroy(sUrl, sDivId) {
  if( confirm(lang.confirm_destroy) ) {
    if($('#' + sDivId).length) {
      $('#' + sDivId).effect("highlight", {
        mode: 'hide'
      }, 2000);

      $.post(sUrl + '.json?method=delete');
    }
    else {
      parent.location.href = sUrl + '?method=delete';
    }
  }
}

/**
 * Count the length of content.
 *
 * @param string sDivId DIV to get data from
 * @param integer iLen maximum length
 *
 */
function countCharLength(sDivId, iLen) {
  var oInput  = $(sDivId);
  var iLength = iLen === undefined ? oInput.prop('maxlength') : iLen;
  $(sDivId).next().html(iLength - oInput.val().length);
}

/**
 *
 * Calculate the total upload size of all files
 *
 * @param object fileInput object with all information
 * @return integer total file size
 *
 */
function getSizeOfFiles(fileInput) {
  if (typeof window.FileReader !== 'function' || !fileInput.files || !fileInput.files[0]) {
    return 0;
  }

  var iFileSize = 0;
  var iLength = fileInput.files.length;
  for (var index = 0; index < iLength; index++) {
    iFileSize = iFileSize + fileInput.files[index].size;
  }

  return iFileSize;
}

function checkFileSize(fileInput, iMaxFileSize, sMessage) {
  var iFileSize     = getSizeOfFiles(fileInput[0]);
  var jControlGroup = fileInput.closest('.control-group');
  var jHelpId       = 'file-input-help';

  if (iFileSize > iMaxFileSize) {
    jControlGroup.addClass('alert alert-error');
    if ($('#' + jHelpId).length) {
      $('#' + jHelpId).removeClass('invisible');
    }
    else {
      var jHelp = $('<span class="help-inline" id="' + jHelpId + '">' + sMessage + '</span>');
      fileInput.after(jHelp);
    }
  }
  else {
    $('#' + jHelpId).fadeOut(function() {
      $(this).remove();
      if (!jControlGroup.find('.help-inline').length) {
        jControlGroup.removeClass('alert alert-error');
      }
    });
  }
}

/**
 *
 *
 */
var iCounter = 0;
function enableInfiniteScroll(selector, itemselector, repeatTimes, pathImages) {
  $(selector).infinitescroll({
    navSelector   : 'div.pagination',
    nextSelector  : 'div.pagination a:first',
    itemSelector  : itemselector,
    loading       : {msgText : '',
      img         : pathImages + '/candy.global/loading.gif',
      finishedMsg : '',
      selector    : 'div.js-pagination',
      finished    : function(opts){
        opts.loading.msg.fadeOut(opts.loading.speed);
        iCounter = iCounter + 1;
        if (iCounter % repeatTimes === 0){
          /** if we did load a few times, we want to stop and display a resume button **/
          opts.contentSelector.infinitescroll('pause');
          var a = $('<a alt="' + lang.pages.more + '" data-role="button" class="btn">' + lang.pages.more + '</a>');
          a.click(function(){
            $(this).fadeOut( opts.loading.speed, function(){
              opts.contentSelector.infinitescroll('resume');
            });
          });
          $(opts.loading.selector).append(a);
        }
        return true;
      }
    },
    animate: true
  });
}

/**
 *
 * Reset all upload information.
 *
 */
function prepareForUpload() {
  $('#js-progress_bar').css('width', '0%');
  $('#js-progress').toggle();
};

/**
 *
 *
 *
 */
function upload(e, url, controller, inputId, dependencyId, reloadUrl) {
  // Remove old error messages and helping texts
  $('.control-group').removeClass('alert alert-error');

  var files = document.querySelector('#input-' + inputId).files;
  var fd = new FormData();

  for (var i = 0, fileObject; fileObject = files[i]; ++i) {
    fd.append('file[' + i + ']', fileObject);
  }

  if(dependencyId.length) {
    $('#input-' + dependencyId).click(function() {
      $(this).closest().removeClass('alert alert-error');
      $('.help-inline').remove();
    });

    // Additional information fields
    $.each($('form').serializeArray(), function(i, field) {
      fd.append(field.name, field.value);
    });
  }

  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/' + url + '.json', true);

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      var percentComplete = (e.loaded / e.total) * 100;
      $('#js-progress_bar').css('width', percentComplete + '%');
    }
  };

  xhr.onload = function() {
    $('#js-progress').toggle();

    var aJson = JSON.parse(this.response);

    if(aJson.success == true) {
      var message = lang.upload_successful;

      if(controller == 'medias' || controller == 'downloads' || controller == 'galleries') {
        message = message + ' ' + lang.reloading;
        $('.form-horizontal').toggle();
      }
      else if(controller == 'users') {
        $('#js-avatar_thumb').attr('src', aJson.fileData.thumbnail);
        $('#js-avatar_link').attr('href', aJson.fileData.popup);
      }

      $('.control-group input').val('');
      showFlashMessage('success', message);

      // Reload to easily show images
      if(reloadUrl == true)
        setTimeout(function() {location.reload()}, 3000);
    }
    else {
      if (aJson.error) {
        $.each(aJson.error, function(index, value) {
          $('#input-' + index).closest('.control-group').addClass('alert alert-error');
          $('#input-' + index).parent().append("<span class='help-inline'>" + value + "</span>");
          $('#input-' + index).next().remove();
        });
      }

      showFlashMessage('error', lang.upload_error);
    }
  };

  xhr.send(fd);
}

/**
 *
 * @param string sDivId DIV where information will be appended at
 * @param string sController controller to work with
 * @param string sActionAndIdInformation method or id/method to work with
 *
 */
function showAjaxUpload(sDivId, sController, sActionAndIdInformation) {
  $('p.center a').click(function() {
    if($('#' + sDivId).length == 0) {
      $('.page-header').after("<div id='" + sDivId + "'></div>");
      $('#' + sDivId).load('/' + sController + '/' + sActionAndIdInformation + '.ajax');
    }
    else {
      $('.form-horizontal').toggle();
    }
  });
}

/**
 *
 * @param string sStatus status type (alert, success or warning)
 * @param string sMessage message to display
 *
 */
function showFlashMessage(sStatus, sMessage) {
  $('#js-flash_message').show().children().attr('id', 'js-flash_' + sStatus).attr('class', 'alert alert-' + sStatus);
  $('#js-flash_' + sStatus + ' a').remove();
  $('#js-flash_' + sStatus + ' p').html(sMessage);
  $('#js-flash_message').delay('10000').slideUp();
}

function show(sDivId) {
  $(sDivId).show();

  if($('#js-flash_success') || $('#js-flash_error')) {
    hide(sDivId, 10000);
  }
}

/* Hide div */
function hide(sDivId, iDelay) {
  $(sDivId).delay(iDelay).slideUp();
}

/* ToggleOpacity */
$.fn.toggleOpacity = function (t) {
  if(t)
    this.stop(true,true).animate({
      opacity:1
    });
  else
    this.stop(true,true).animate({
      opacity:0.25
    });
}

/* Show success and error messages */
if($('#js-flash_success, #js-flash_error, #js-flash_warning').length) {
  show('#js-flash_message');
}

$('#js-flash_success, #js-flash_error, #js-flash_warning').click(function() {
  hide(this, 0);
});


/**
 *
 *
 *
 */
if ($('.js-tooltip').length)
  $('.js-tooltip').tooltip();

/**
 *
 **/
if ($('p.error').length)
  $('p.error').tooltip();

/**
 *
 */
$('input[maxlength]').each(function() {
  $(this).bind('keyup', function() {
    countCharLength(this);
  });
});
