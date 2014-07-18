jQuery(function() {

  var doc;
  var iphone;
  var windowWidth;
  var windowHeight;
  var pageHeight;
  //var contentPadding;
  var iPhonestop;
  var featuresList;
  var navfeatureLinks;
  var featuredSection;
  var currentActive;
  var topCache;

  var initialize = function  () {
    currentActive        = 0;
    topCache             = [];
    win                  = jQuery(window);
    doc                  = jQuery(document);
    bod                  = jQuery(document.body);
    iphone               = iphone || jQuery('.iphone');
    featuresList         = jQuery('.features-list');
    featureLinks         = jQuery('.feature-example a');
    featuredSection       = jQuery('.feature');
    topCache             = featuredSection.map(function () { return jQuery(this).offset().top });
    windowHeight         = jQuery(window).height() / 3;
    pageHeight           = jQuery(document).height();
    //contentPadding       = parseInt(jQuery('.docs-content').css('padding-bottom'));
    iPhonestop         = jQuery('#large-header-block').height() + jQuery('body > .container').height();
    iphone.initialLeft   = iphone.offset().left;
    iphone.initialTop    = iphone.initialTop || iphone.offset().top;
    iphone.iphoneStartPosition = (jQuery(window).height() + 20 + jQuery('.docs-masthead').height() - iphone.height())/2;
    calculateScroll();
    //console.log(topCache);

    iphone.on('click', function (e) {
      e.preventDefault();
    });
    doc.on('click', function () {
        featuresList.removeClass('active');
      })
      win.on('scroll', calculateScroll);
    }




  var calculateScroll = function() {
    // Save scrollTop value
    var featuredSectionItem;
    var currentTop = win.scrollTop();

    // Stop iPhone at bottom
    //console.log(iPhonestop - currentTop);
    //console.log(iphone.iphoneStartPosition);
    //console.log(currentTop);
    //console.log(iPhonestop);
    if(currentTop > (iPhonestop - iphone.iphoneStartPosition - 711 - 130)) {
      iphone[0].className = "iphone iphone-bottom";
      iphone[0].setAttribute('style','');
    } else if((iphone.initialTop - currentTop) <= iphone.iphoneStartPosition) {
      iphone[0].className = "iphone iphone-fixed";
      iphone.css({top: iphone.iphoneStartPosition});
    } else {
      iphone[0].className = "iphone";
      iphone[0].setAttribute('style','');
    }

    // Injection of features into phone
    for (var l = featuredSection.length; l--;) {
      if ((topCache[l] - currentTop - 200) < windowHeight) {
        if (currentActive == l) return;
        currentActive = l;
        bod.find('.feature.active').removeClass('active');
        featuredSectionItem = jQuery(featuredSection[l])
        featuredSectionItem.addClass('active');
        if(featuredSectionItem.attr('id')) {
          iphone.attr("id", featuredSectionItem.attr('id') + "InPhone");
        } else {
          iphone.attr("id", "");
        }
        if (!featuredSectionItem.hasClass('informational')) {
          updateContent(featuredSectionItem.find('.displaypiece').not('.js').html())
        }
        break
      }
    }

    function updateContent(content) {
      jQuery('#iwindow').html(content);
    }
  }

  jQuery(window).on('load resize', initialize);
  //jQuery(window).on('load', function () { new FingerBlast('.iphone-content'); });
});