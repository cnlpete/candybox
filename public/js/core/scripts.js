function show(sDivId) {
  $(sDivId).slideDown();

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

/* Quote comment */
function quote(sName, sDivId) {
  var oTextField  = $('#js-create_commment_text');
  var sOldMessage = oTextField.val();
  var sQuote      = $('#' + sDivId).html();
  var sNewMessage = "[quote=" + sName + "]" + sQuote + "[/quote]\n";
  oTextField.val(sOldMessage + sNewMessage);

  if ($.mobile)
    $.mobile.silentScroll(oTextField.offset().top);

  return false;
}

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

function confirmDestroy(sUrl) {
  if(typeof lang == 'undefined') {
    var lang = {
      'confirm_destroy' : 'Do you really want to destroy this entry?'
    };
  }

  if( confirm(lang.confirm_destroy) )
    parent.location.href = sUrl;
}

function countCharLength(sDiv, iLen) {
  var iLength = iLen - $(sDiv).val().length;
  $(sDiv).next().html(iLength);
}

/* calculate the totalUploadSize */
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
        if (iCounter % repeatTimes == 0){
          /** if we did load a few times, we want to stop and display a resume button **/
          opts.contentSelector.infinitescroll('pause');
          var a = $('<a alt="{$lang.pages.more}" data-role="button" class="btn">{$lang.pages.more}</a>');
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
    animate       : true
  });
}

/* Show success and error messages */
if($('#js-flash_success, #js-flash_error, #js-flash_warning').length) {
  show('#js-flash_message');
}

$('#js-flash_success, #js-flash_error, #js-flash_warning').click(function() {
  hide(this, 0);
});

/* Show tooltips */
if ($('.js-tooltip').length)
  $('.js-tooltip').tooltip();

if ($('p.error').length)
  $('p.error').tooltip();

/* AJAX image upload*/
function prepareForUpload() {
  $('#js-progress_bar').css('width', '0%');
  $('#js-progress').fadeIn();
};

function upload(e, url, inputId, dependencyId) {
  // Disable upload button
  $(e).attr('disabled');

  var file = document.querySelector('#input-' + inputId).files[0];
  var fd = new FormData();
  fd.append('file', file);

  if(dependencyId !== '') {
    $('#input-' + dependencyId).click(function() {
      $(this).parents().eq(2).removeClass('alert alert-error');
      $('.help-inline').remove();
    });

    if($('#input-' + dependencyId).is(':checkbox'))
      fd.append(dependencyId, $('#input-' + dependencyId).attr('checked'));
  }

  var xhr = new XMLHttpRequest();
  xhr.open('POST', url, true);

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      var percentComplete = (e.loaded / e.total) * 100;
      $('#js-progress_bar').css('width', percentComplete + '%');
    }
  };

  xhr.onload = function() {
    $('#js-progress').fadeOut();
    $(e).removeAttr('disabled');

    var aJson = JSON.parse(this.response);

    if(aJson.success == true) {
      if($('#js-avatar_thumb'))
        $('#js-avatar_thumb').attr('src', aJson.dataUrl);

      if($('#js-avatar_link'))
        $('#js-avatar_link').attr('href', aJson.fileUrl);

      $('.control-group').removeClass('alert alert-error');
    }
    else {
      console.log(aJson);

      if(aJson.error[dependencyId]) {
        $('#input-' + dependencyId).parents().eq(2).addClass('alert alert-error');
        $('#input-' + dependencyId).parents().eq(1).append("<span class='help-inline'>" + aJson.error[dependencyId] + "</span>");
      }

      if(aJson.error[inputId]) {
        $('#input-' + inputId).parents().eq(1).addClass('alert alert-error');
        $('#input-' + inputId).next().html(aJson.error[inputId]);
      }
    }
  };

  xhr.send(fd);
}

function uploadError() {

}