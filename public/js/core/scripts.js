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
function enableInfiniteScroll(selector, itemselector, repeatTimes) {
  $(selector).infinitescroll({
    navSelector   : 'div.pagination',
    nextSelector  : 'div.pagination a:first',
    itemSelector  : itemselector,
    loading       : { msgText : '',
      img         : '{$_PATH.images}/candy.global/loading.gif',
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
if($('#js-flash_success').length || $('#js-flash_error').length) {
  show('#js-flash_message');
}

$('#js-flash_success').click(function() {
  hide(this, 0);
});

$('#js-flash_error').click(function() {
  hide(this, 0);
});

/* Show tooltips */
if ($('.js-tooltip').length)
  $('.js-tooltip').tooltip();
if ($('p.error').length)
  $('p.error').tooltip();