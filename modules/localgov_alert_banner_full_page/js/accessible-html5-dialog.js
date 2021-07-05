"use strict";

/*
 * accessibility.js - a library with common accessibility related routines.
 * by Zoltan Hawryluk (zoltan.dulac@gmail.com)
 * MIT License.
 * 
 * This file must be transpiled for browser like IE11 using babel
 * Install instructions: https://babeljs.io/docs/en/babel-cli
 * You will also need npx: https://www.npmjs.com/package/npx
 * and the env preset: https://stackoverflow.com/questions/34747693/how-do-i-get-babel-6-to-compile-to-es5-javascript
 */
// add contains polyfill here (for IE11).  The typeof document check is to ensure this 
// script doesn't break server side rendering frameworks like Nashorn.
if (typeof document !== 'undefined' && typeof Element.prototype.contains !== 'function') {
  Element.prototype.contains = function contains(el) {
    return this.compareDocumentPosition(el) % 16;
  };

  document.contains = function docContains(el) {
    return document.body.contains(el);
  };
}
/* global window document */
// This library is not specific to any framework.  It contains utility functions
// that can be used in any project to make it more accessible and assistive
// technology/screenreader friendly.


var accessibility = {
  tempFocusElement: null,
  tabbableSelector: "a[href]:not([tabindex=\"-1\"]):not([disabled]),\n     area[href]:not([tabindex=\"-1\"]):not([disabled]),\n     details:not([tabindex=\"-1\"]):not([disabled]),\n     iframe:not([tabindex=\"-1\"]):not([disabled]),\n     keygen:not([tabindex=\"-1\"]):not([disabled]),\n     [contentEditable=true]:not([tabindex=\"-1\"]):not([disabled]),\n     :enabled:not(fieldset):not([tabindex=\"-1\"]):not([disabled]),\n     object:not([tabindex=\"-1\"]):not([disabled]),\n     embed:not([tabindex=\"-1\"]):not([disabled]),\n     [tabindex]:not([tabindex=\"-1\"]):not([disabled])",
  htmlTagRegex: /(<([^>]+)>)/gi,
  hasSecondaryNavSkipTarget: false,
  // This should set in your project (and outside the script) to be a selector that covers all your main content.
  mainContentSelector: '',
  activeSubdocument: null,
  oldAriaHiddenVal: 'data-old-aria-hidden',

  /**
   * Focuses on an element, and scrolls the window if there is an element on
   * top of the focused element so the user can see what is being focused.
   *
   * @param {object} element - The element being focused
   */
  focusAndScrollToView: function focusAndScrollToView(element) {
    element.focus();
    var elementRect = element.getBoundingClientRect();
    var elementOnTop = document.elementFromPoint(elementRect.left, elementRect.top);

    if (elementOnTop && elementOnTop !== element) {
      var topElRect = elementOnTop.getBoundingClientRect();
      window.scrollBy(0, topElRect.top - topElRect.bottom);
    }
  },

  /**
   * Focuses the first invalid field in a form, so that a screen reader can
   * say the error (assuming the aria-labelledby attribute points to an
   * error message).
   *
   * @param {HTMLElement} formElement - the DOM node of the form element
   * @param {object} options - an optional set of options:
   *   - firstValid: if set to true, this will force the form to focus on the
   *     first formField, whether it is invalid or not
   *   - isAjaxForm: will ensure form does not submit.
   *   - e: the event object of a form event (usually a submit event,
   *     so we can cancel it when isAjaxForm is true).
   * @returns {boolean} - true if there is an error being focused on, false
   *   otherwise
   */
  applyFormFocus: function applyFormFocus(formElement) {
    var _this = this;

    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    var firstValid = options.firstValid,
        isAjaxForm = options.isAjaxForm,
        e = options.e;
    var isFormInvalid = false;

    if (isAjaxForm) {
      e.preventDefault();
    }

    if (formElement instanceof window.HTMLElement) {
      var formFields = formElement.elements;

      var _loop = function _loop(i) {
        var formField = formFields[i]; // If the form is invalid, we must focus the first invalid one (or
        // the first valid one if option.firstValue === true). Since fieldsets
        // are part of the elements array, we must exclude those.

        if (formField.nodeName !== 'FIELDSET' && (firstValid || formField.getAttribute('aria-invalid') === 'true')) {
          isFormInvalid = true;

          if (document.activeElement === formField) {
            _this.focusAndScrollToView(formFields[i + 1]); // If we do not pause for half a second, Voiceover will not read out
            // where it is focused.  There doesn't seem to be any other
            // workaround for this.


            setTimeout(function () {
              if (formField) {
                _this.focusAndScrollToView(formField);
              }
            }, 500);
          } else {
            _this.focusAndScrollToView(formField);
          }

          return "break";
        }
      };

      for (var i = 0; i < formFields.length; i += 1) {
        var _ret = _loop(i);

        if (_ret === "break") break;
      }

      if (!isFormInvalid) {
        // Ensure what is being painted right now is complete to ensure we can
        // grab the first error.
        window.requestAnimationFrame(function () {
          var globalError = formElement.querySelector('.form-error__error-text');

          if (globalError) {
            _this.focusAndScrollToView(globalError);
          }
        });
      }
    }

    return isFormInvalid;
  },
  refocusCurrentElement: function refocusCurrentElement(callback) {
    var _document = document,
        activeElement = _document.activeElement;

    if (this.tempFocusElement && activeElement) {
      this.tempFocusElement.focus(); // If we do not pause for half a second, Voiceover will not read out
      // where it is focused.  There doesn't seem to be any other
      // workaround for this.

      setTimeout(function () {
        if (activeElement) {
          activeElement.focus();

          if (callback) {
            callback();
          }
        }
      }, 500);
    }
  },
  doIfBlurred: function doIfBlurred(e, func) {
    // The `requestAnimationFrame` is needed since the browser doesn't know
    // what the focus is being switched *to* until after a repaint.
    window.requestAnimationFrame(this.doIfBlurredHelper.bind(this, e.currentTarget, e.relatedTarget, func));
  },
  doIfBlurredHelper: function doIfBlurredHelper(currentTarget, relatedTarget, func) {
    var focusedElement = relatedTarget || document.activeElement;
    var isFocusLost = focusedElement.parentNode === document.body || focusedElement === document.body || focusedElement === null;
    /*
     * If a user clicks anywhere within the target that isn't a button, it
     * shouldn't execute `func()` .  This happens also should happen when focus is
     * lost (which is what the `isFocusLost` variable keeps track of).
     *
     * If we blurred out of the target, then we execute the function.
     */

    if (!isFocusLost && !currentTarget.contains(focusedElement)) {
      func();
    }
  },

  /**
   *
   * Strips HTML tags from a string.
   *
   * @param {String} html - a string of HTML code
   */
  removeHTML: function removeHTML(html) {
    return html.replace(this.htmlTagRegex, '');
  },

  /**
   * Converts a string or JSX to lower case with HTML removed,
   * so that it can be read by a screen reader via aria-labels.
   *
   * @param {String} s - a string or JSX that should be converted to lower case.
   */
  toLowerCase: function toLowerCase(s) {
    var r = '';

    if (s) {
      if (s.toString) {
        r = this.removeHTML(s.toString().toLowerCase());
      } else if (s.toLowerCase) {
        r = this.removeHTML(s.toLowerCase());
      }
    }

    return r;
  },

  /**
   * Hides the main content from assistive technologies (like screen readers).
   * This is useful when you want to make sure the main content is not
   * accessible to AT on certain devices, like the iOS Voiceover screen reader
   * when a flyout is open, to ensure the user doesn't accidentally access
   * the main content when the "blur" outside of the main menu.
   *
   * @param {Boolean} s - the visibility of the main content.
   *
   */
  setMainContentAriaHidden: function setMainContentAriaHidden(value) {
    var els = document.querySelectorAll(this.mainContentSelector);

    for (var i = 0; i < els.length; i++) {
      var el = els[i]; // setting the aria-hidden attribute to 'false' would make the element
      // accessible to Voiceover, it just wouldn't be able to read it.
      // This is why we set it to `null`.

      if (value) {
        el.setAttribute('aria-hidden', value);
      } else {
        el.removeAttribute('aria-hidden');
      }
    }
  },

  /**
   * Detects while element has been blurred inside the active subdocument (e.g. a modal).
   * If it is the first, then we focus the last element.
   * If it is the last, then we focus the first.
   *
   * Note that this only works in non-mobile devices, since mobile
   * devices don't track focus and blur events.
   *
   * @param {HTMLElement} blurredEl
   */
  keepFocusInsideActiveSubdoc: function keepFocusInsideActiveSubdoc(blurredEl) {
    if (!this.activeSubdocument) {
      return;
    }

    var allowableFocusableEls = this.activeSubdocument.querySelectorAll(this.tabbableSelector);
    var firstFocusableElement = allowableFocusableEls[0];
    var lastFocusableElement = allowableFocusableEls[allowableFocusableEls.length - 1];
    console.log(this.tabbableSelector, allowableFocusableEls);

    if (blurredEl === firstFocusableElement) {
      lastFocusableElement.focus();
    } else {
      firstFocusableElement.focus();
    }
  },

  /**
   * Detects when focus is being blurred out ofthe activeSubdocument of a
   * page (e.g. an open modal).  If it is, it executes a callback, func.
   *
   * @param {HTMLElement} blurredEl
   * @param {function} func
   */
  doWhenActiveSubdocIsBlurred: function doWhenActiveSubdocIsBlurred(blurredEl, func) {
    var activeSubdocument = this.activeSubdocument;

    if (activeSubdocument) {
      window.requestAnimationFrame(function () {
        var _document2 = document,
            activeElement = _document2.activeElement;

        if (activeElement !== null && !activeSubdocument.contains(activeElement)) {
          func(blurredEl);
        }
      });
    }
  },

  /**
   * A blur event handler to that will create a focus loop
   * inside the activeSubdocument (e.g. a modal).
   *
   * @param {EventHandler} e - the blur event handler
   */
  testIfFocusIsOutside: function testIfFocusIsOutside(e) {
    var blurredEl = e.target;
    var activeSubdocument = this.activeSubdocument;

    if (activeSubdocument) {
      this.doWhenActiveSubdocIsBlurred(blurredEl, this.keepFocusInsideActiveSubdoc.bind(this));
    }
  },

  /**
   * A focus event that will be activated when, say, a modal
   * is open.  When a modal (a.k.a. activeSubdocument) is open,
   * and focus goes outside of that element, we put focus to the
   * first element.
   *
   * @param {EventHandler} e - a focus event handler.
   */
  correctFocusFromBrowserChrome: function correctFocusFromBrowserChrome(e) {
    var activeSubdocument = this.activeSubdocument,
        tabbableSelector = this.tabbableSelector;
    var _document3 = document,
        activeElement = _document3.activeElement;
    var relatedTarget = e.relatedTarget;

    if (activeSubdocument && relatedTarget === null && !activeSubdocument.contains(activeElement)) {
      var allowableFocusableEls = activeSubdocument.querySelectorAll(tabbableSelector);

      if (allowableFocusableEls.length > 0) {
        var firstFocusableElement = allowableFocusableEls[0];
        firstFocusableElement.focus();
      }
    }
  },

  /**
   * This ensures that a mobile devices "accessibilityFocus"
   * (which is independant from a browser focus) cannot
   * go outside an element, by ensuring
   * the least amount of nodes outside the modal are
   * marked with aria-hidden="true".
   *
   * @param {HTMLElement} el - the element that will have the loop.
   */
  setMobileFocusLoop: function setMobileFocusLoop(el) {
    var _document4 = document,
        body = _document4.body;
    var currentEl = el; // If there are any nodes with oldAriaHiddenVal set, we should
    // bail, since it has already been done.

    var hiddenEl = document.querySelector("[".concat(this.oldAriaHiddenVal, "]"));

    if (hiddenEl !== null) {
      // eslint-disable-next-line no-console
      console.warn('Attempted to run setMobileFocusLoop() twice in a row.  removeMobileFocusLoop() must be executed before it run again. Bailing.');
      return;
    }

    do {
      // for every sibling of currentElement, we mark with
      // aria-hidden="true".
      var siblings = currentEl.parentNode.childNodes;

      for (var i = 0; i < siblings.length; i++) {
        var sibling = siblings[i];

        if (sibling !== currentEl && sibling.setAttribute) {
          sibling.setAttribute(this.oldAriaHiddenVal, sibling.ariaHidden || 'null');
          sibling.setAttribute('aria-hidden', 'true');
        }
      } // we then set the currentEl to be the parent node
      // and repeat (unless the currentNode is the body tag).


      currentEl = currentEl.parentNode;
    } while (currentEl !== body);
  },

  /**
   * reset all the nodes that have been marked as aria-hidden="true"
   * in the setMobileFocusLoop() method back to their original
   * aria-hidden values.
   */
  removeMobileFocusLoop: function removeMobileFocusLoop() {
    var elsToReset = document.querySelectorAll("[".concat(this.oldAriaHiddenVal, "]"));

    for (var i = 0; i < elsToReset.length; i++) {
      var el = elsToReset[i];
      var ariaHiddenVal = el.getAttribute(this.oldAriaHiddenVal);

      if (ariaHiddenVal === 'null') {
        el.removeAttribute('aria-hidden');
      } else {
        el.setAttribute('aria-hidden', ariaHiddenVal);
      }

      el.removeAttribute(this.oldAriaHiddenVal);
    }
  },

  /**
   *
   * Produces and removes a focus loop inside an element
   *
   * @param {HTMLElement} el - the element in question
   * @param {boolean} keepFocusInside - true if we need to create a loop, false otherwise.
   */
  setKeepFocusInside: function setKeepFocusInside(el, keepFocusInside) {
    var _document5 = document,
        body = _document5.body;

    if (keepFocusInside) {
      this.activeSubdocument = el;
      body.addEventListener('blur', this.testIfFocusIsOutside.bind(this), true);
      body.addEventListener('focus', this.correctFocusFromBrowserChrome.bind(this), true);
      this.setMobileFocusLoop(el);
    } else {
      this.activeSubdocument = null;
      body.removeEventListener('blur', this.testIfFocusIsOutside.bind(this), true);
      body.removeEventListener('focus', this.correctFocusFromBrowserChrome.bind(this), true);
      this.removeMobileFocusLoop(el);
    }
  },

  /**
   * Takes the *positive* modulo of n % m.  Javascript will
   * return negative ones if n < 0.
   */
  mod: function mod(n, m) {
    return (n % m + m) % m;
  },

  /**
   * Makes the arrow keys work on a radiogroup's radio buttons.
   *
   * @param {HTMLElement} el - the radiogroup in question.
   * @param {object} options - an optional set of options:
   *
   * - allowTabbing: if set to true, allows tabbing of the individual
   *   radio buttons with the tab key.  This is useful when the radio
   *   buttons don't look like radio buttons.
   * - doKeyChecking: if set to true, then this allows the space and
   *   the enter key to allow checking of the radio button.
   * - setState: if set to false, then the library doesn't set the
   *   state.  It is assumed that `ariaCheckedCallback` will do the
   *   setting of state of the checkbox instead (this is useful in
   *   frameworks like React). Default is true.
   * - ariaCheckedCallback: a callback to run when an element is checked.
   *   The following parameters will be passed to it:
   *   - el (the element that was checked),
   *   - index (the index of the radio element within the radiogroup),
   *   - prevCheckedIndex (the index of the radio element that was previously checked)
   *   - group (the radiogroup that el is contained in)
   * - radioFocusCallback: a callback to run when a radio button is focused.
   *   The following parameters will be passed to it:
   *   - el (the element that was checked),
   *   - group (the radiogroup that el is contained in)
   */
  setArrowKeyRadioGroupEvents: function setArrowKeyRadioGroupEvents(el, options) {
    var _ref = options || {},
        allowTabbing = _ref.allowTabbing,
        doKeyChecking = _ref.doKeyChecking,
        ariaCheckedCallback = _ref.ariaCheckedCallback,
        setState = _ref.setState,
        radioFocusCallback = _ref.radioFocusCallback;

    el.dataset.allowTabbing = !!allowTabbing;
    el.dataset.doKeyChecking = !!doKeyChecking;
    el.dataset.setState = setState === false ? false : true;
    el.ariaCheckedCallback = ariaCheckedCallback;
    el.radioFocusCallback = radioFocusCallback;
    el.addEventListener('keydown', this.radioGroupKeyUpEvent.bind(this), true);
    /* if (radioFocusCallback) {
      el.addEventListener('focus', this.radioGroupFocusEvent.bind(this), true);
    } */
  },

  /**
   *
   * Checks an ARIA radio button, while unchecking the others in its radiogroup.
   *
   * @param {HTMLElement} radioEl - a radio button that needs to be checked
   * @param {Array} radioGroupEls - an array of radio buttons that is in the same group as radioEl
   */
  checkRadioButton: function checkRadioButton(e, radioEl, radioGroupEls, setState, ariaCheckedCallback) {
    var previouslyCheckedEl;
    var currentlyCheckedEl;
    var currentlyCheckedIndex;

    for (var i = 0; i < radioGroupEls.length; i++) {
      var currentRadio = radioGroupEls[i];
      var checkedState = 'false';

      if (currentRadio.getAttribute('aria-checked') === 'true') {
        previouslyCheckedEl = currentRadio;
      }

      if (currentRadio === radioEl) {
        if (setState) {
          checkedState = 'true';
        }

        currentlyCheckedEl = currentRadio;
        currentlyCheckedIndex = i;
      }

      if (setState) {
        currentRadio.setAttribute('aria-checked', checkedState);
      }
    }

    if (ariaCheckedCallback) {
      ariaCheckedCallback(e, currentlyCheckedEl, currentlyCheckedIndex, previouslyCheckedEl, radioGroupEls);
    }
  },
  radioGroupFocusEvent: function radioGroupFocusEvent(e) {
    var target = e.target,
        currentTarget = e.currentTarget;
    var radioFocusCallback = currentTarget.radioFocusCallback;
    var radioEls = Array.from(currentTarget.querySelectorAll('[role="radio"]'));
    var targetIndex = radioEls.indexOf(target);

    if (radioFocusCallback) {
      radioFocusCallback(e, target, targetIndex, currentTarget);
    }
  },

  /**
   * Implements keyboard events for ARIA radio buttons.
   *
   * @param {Event} e - the keyboard event.
   */
  radioGroupKeyUpEvent: function radioGroupKeyUpEvent(e) {
    var key = e.key,
        target = e.target,
        currentTarget = e.currentTarget,
        shiftKey = e.shiftKey;
    var ariaCheckedCallback = currentTarget.ariaCheckedCallback,
        dataset = currentTarget.dataset;
    var allowTabbing = dataset.allowTabbing,
        doKeyChecking = dataset.doKeyChecking,
        setState = dataset.setState;
    allowTabbing = allowTabbing === 'true';
    setState = setState === 'true';
    doKeyChecking = doKeyChecking === 'true';

    if (target.getAttribute('role') === 'radio') {
      var radioEls = Array.from(currentTarget.querySelectorAll('[role="radio"]'));
      var targetIndex = radioEls.indexOf(target);
      var elToFocus;

      if (targetIndex >= 0) {
        switch (key) {
          case 'ArrowUp':
          case 'ArrowLeft':
            elToFocus = radioEls[this.mod(targetIndex - 1, radioEls.length)];
            this.checkRadioButton(e, elToFocus, radioEls, setState, ariaCheckedCallback);
            break;

          case 'ArrowDown':
          case 'ArrowRight':
            elToFocus = radioEls[this.mod(targetIndex + 1, radioEls.length)];
            this.checkRadioButton(e, elToFocus, radioEls, setState, ariaCheckedCallback);
            break;

          case 'Tab':
            if (!allowTabbing) {
              var tabbableEls = Array.from(document.querySelectorAll(this.tabbableSelector));
              var tabbableElsWithoutRadios = tabbableEls.filter(function (el) {
                return el === target || radioEls.indexOf(el) < 0;
              });
              var tabbableIndex = Array.from(tabbableElsWithoutRadios).indexOf(target);

              if (shiftKey) {
                elToFocus = tabbableElsWithoutRadios[this.mod(tabbableIndex - 1, tabbableElsWithoutRadios.length)];
              } else {
                elToFocus = tabbableElsWithoutRadios[this.mod(tabbableIndex + 1, tabbableElsWithoutRadios.length)];
              }
            }

            break;

          case ' ':
            if (doKeyChecking) {
              this.checkRadioButton(e, target, radioEls, setState, ariaCheckedCallback);
              e.preventDefault();
            }

            break;

          default:
        }

        if (elToFocus) {
          e.preventDefault();
          requestAnimationFrame(function () {
            elToFocus.focus();

            if (key === 'Tab') {
              requestAnimationFrame(function () {
                this.radioGroupFocusEvent(e);
              });
            }
          });
        }
      }
    }
  }
};
(function() {

    // nb. This is for IE10 and lower _only_.
    var supportCustomEvent = window.CustomEvent;
    if (!supportCustomEvent || typeof supportCustomEvent === 'object') {
      supportCustomEvent = function CustomEvent(event, x) {
        x = x || {};
        var ev = document.createEvent('CustomEvent');
        ev.initCustomEvent(event, !!x.bubbles, !!x.cancelable, x.detail || null);
        return ev;
      };
      supportCustomEvent.prototype = window.Event.prototype;
    }
  
    /**
     * @param {Element} el to check for stacking context
     * @return {boolean} whether this el or its parents creates a stacking context
     */
    function createsStackingContext(el) {
      while (el && el !== document.body) {
        var s = window.getComputedStyle(el);
        var invalid = function(k, ok) {
          return !(s[k] === undefined || s[k] === ok);
        }
        if (s.opacity < 1 ||
            invalid('zIndex', 'auto') ||
            invalid('transform', 'none') ||
            invalid('mixBlendMode', 'normal') ||
            invalid('filter', 'none') ||
            invalid('perspective', 'none') ||
            s['isolation'] === 'isolate' ||
            s.position === 'fixed' ||
            s.webkitOverflowScrolling === 'touch') {
          return true;
        }
        el = el.parentElement;
      }
      return false;
    }
  
    /**
     * Finds the nearest <dialog> from the passed element.
     *
     * @param {Element} el to search from
     * @return {HTMLDialogElement} dialog found
     */
    function findNearestDialog(el) {
      while (el) {
        if (el.localName === 'dialog') {
          return /** @type {HTMLDialogElement} */ (el);
        }
        el = el.parentElement;
      }
      return null;
    }
  
    /**
     * Blur the specified element, as long as it's not the HTML body element.
     * This works around an IE9/10 bug - blurring the body causes Windows to
     * blur the whole application.
     *
     * @param {Element} el to blur
     */
    function safeBlur(el) {
      if (el && el.blur && el !== document.body) {
        el.blur();
      }
    }
  
    /**
     * @param {!NodeList} nodeList to search
     * @param {Node} node to find
     * @return {boolean} whether node is inside nodeList
     */
    function inNodeList(nodeList, node) {
      for (var i = 0; i < nodeList.length; ++i) {
        if (nodeList[i] === node) {
          return true;
        }
      }
      return false;
    }
  
    /**
     * @param {HTMLFormElement} el to check
     * @return {boolean} whether this form has method="dialog"
     */
    function isFormMethodDialog(el) {
      if (!el || !el.hasAttribute('method')) {
        return false;
      }
      return el.getAttribute('method').toLowerCase() === 'dialog';
    }
  
    /**
     * @param {!HTMLDialogElement} dialog to upgrade
     * @constructor
     */
    function dialogPolyfillInfo(dialog) {
      this.dialog_ = dialog;
      this.replacedStyleTop_ = false;
      this.openAsModal_ = false;
  
      // Set a11y role. Browsers that support dialog implicitly know this already.
      if (!dialog.hasAttribute('role')) {
        dialog.setAttribute('role', 'dialog');
      }
  
      dialog.show = this.show.bind(this);
      dialog.showModal = this.showModal.bind(this);
      dialog.close = this.close.bind(this);
  
      if (!('returnValue' in dialog)) {
        dialog.returnValue = '';
      }
  
      if ('MutationObserver' in window) {
        var mo = new MutationObserver(this.maybeHideModal.bind(this));
        mo.observe(dialog, {attributes: true, attributeFilter: ['open']});
      } else {
        // IE10 and below support. Note that DOMNodeRemoved etc fire _before_ removal. They also
        // seem to fire even if the element was removed as part of a parent removal. Use the removed
        // events to force downgrade (useful if removed/immediately added).
        var removed = false;
        var cb = function() {
          removed ? this.downgradeModal() : this.maybeHideModal();
          removed = false;
        }.bind(this);
        var timeout;
        var delayModel = function(ev) {
          if (ev.target !== dialog) { return; }  // not for a child element
          var cand = 'DOMNodeRemoved';
          removed |= (ev.type.substr(0, cand.length) === cand);
          window.clearTimeout(timeout);
          timeout = window.setTimeout(cb, 0);
        };
        ['DOMAttrModified', 'DOMNodeRemoved', 'DOMNodeRemovedFromDocument'].forEach(function(name) {
          dialog.addEventListener(name, delayModel);
        });
      }
      // Note that the DOM is observed inside DialogManager while any dialog
      // is being displayed as a modal, to catch modal removal from the DOM.
  
      Object.defineProperty(dialog, 'open', {
        set: this.setOpen.bind(this),
        get: dialog.hasAttribute.bind(dialog, 'open')
      });
  
      this.backdrop_ = document.createElement('div');
      this.backdrop_.className = 'backdrop';
      this.backdrop_.addEventListener('click', this.backdropClick_.bind(this));
    }
  
    dialogPolyfillInfo.prototype = {
  
      get dialog() {
        return this.dialog_;
      },
  
      /**
       * Maybe remove this dialog from the modal top layer. This is called when
       * a modal dialog may no longer be tenable, e.g., when the dialog is no
       * longer open or is no longer part of the DOM.
       */
      maybeHideModal: function() {
        if (this.dialog_.hasAttribute('open') && document.body.contains(this.dialog_)) { return; }
        this.downgradeModal();
      },
  
      /**
       * Remove this dialog from the modal top layer, leaving it as a non-modal.
       */
      downgradeModal: function() {
        if (!this.openAsModal_) { return; }
        this.openAsModal_ = false;
        this.dialog_.style.zIndex = '';
  
        // This won't match the native <dialog> exactly because if the user set top on a centered
        // polyfill dialog, that top gets thrown away when the dialog is closed. Not sure it's
        // possible to polyfill this perfectly.
        if (this.replacedStyleTop_) {
          this.dialog_.style.top = '';
          this.replacedStyleTop_ = false;
        }
  
        // Clear the backdrop and remove from the manager.
        this.backdrop_.parentNode && this.backdrop_.parentNode.removeChild(this.backdrop_);
        dialogPolyfill.dm.removeDialog(this);
      },
  
      /**
       * @param {boolean} value whether to open or close this dialog
       */
      setOpen: function(value) {
        if (value) {
          this.dialog_.hasAttribute('open') || this.dialog_.setAttribute('open', '');
        } else {
          this.dialog_.removeAttribute('open');
          this.maybeHideModal();  // nb. redundant with MutationObserver
        }
      },
  
      /**
       * Handles clicks on the fake .backdrop element, redirecting them as if
       * they were on the dialog itself.
       *
       * @param {!Event} e to redirect
       */
      backdropClick_: function(e) {
        if (!this.dialog_.hasAttribute('tabindex')) {
          // Clicking on the backdrop should move the implicit cursor, even if dialog cannot be
          // focused. Create a fake thing to focus on. If the backdrop was _before_ the dialog, this
          // would not be needed - clicks would move the implicit cursor there.
          var fake = document.createElement('div');
          this.dialog_.insertBefore(fake, this.dialog_.firstChild);
          fake.tabIndex = -1;
          fake.focus();
          this.dialog_.removeChild(fake);
        } else {
          this.dialog_.focus();
        }
  
        var redirectedEvent = document.createEvent('MouseEvents');
        redirectedEvent.initMouseEvent(e.type, e.bubbles, e.cancelable, window,
            e.detail, e.screenX, e.screenY, e.clientX, e.clientY, e.ctrlKey,
            e.altKey, e.shiftKey, e.metaKey, e.button, e.relatedTarget);
        this.dialog_.dispatchEvent(redirectedEvent);
        e.stopPropagation();
      },
  
      /**
       * Focuses on the first focusable element within the dialog. This will always blur the current
       * focus, even if nothing within the dialog is found.
       */
      focus_: function() {
        // Find element with `autofocus` attribute, or fall back to the first form/tabindex control.
        var target = this.dialog_.querySelector('[autofocus]:not([disabled])');
        if (!target && this.dialog_.tabIndex >= 0) {
          target = this.dialog_;
        }
        if (!target) {
          /*
          // Note that this is 'any focusable area'. This list is probably not exhaustive, but the
          // alternative involves stepping through and trying to focus everything.
          var opts = ['button', 'input', 'keygen', 'select', 'textarea'];
          var query = opts.map(function(el) {
            return el + ':not([disabled])';
          });
          // TODO(samthor): tabindex values that are not numeric are not focusable.
          query.push('[tabindex]:not([disabled]):not([tabindex=""])');  // tabindex != "", not disabled
          target = this.dialog_.querySelector(query.join(', '));
          */
         target = this.dialog_.querySelector(accessibility.tabbableSelector);
        }
        safeBlur(document.activeElement);
        target && target.focus();
      },
  
      /**
       * Sets the zIndex for the backdrop and dialog.
       *
       * @param {number} dialogZ
       * @param {number} backdropZ
       */
      updateZIndex: function(dialogZ, backdropZ) {
        if (dialogZ < backdropZ) {
          throw new Error('dialogZ should never be < backdropZ');
        }
        this.dialog_.style.zIndex = dialogZ;
        this.backdrop_.style.zIndex = backdropZ;
      },
  
      /**
       * Shows the dialog. If the dialog is already open, this does nothing.
       */
      show: function() {
        if (!this.dialog_.open) {
          this.setOpen(true);
          this.focus_();
        }
      },
  
      /**
       * Show this dialog modally.
       */
      showModal: function() {
        if (this.dialog_.hasAttribute('open')) {
          throw new Error('Failed to execute \'showModal\' on dialog: The element is already open, and therefore cannot be opened modally.');
        }
        if (!document.body.contains(this.dialog_)) {
          throw new Error('Failed to execute \'showModal\' on dialog: The element is not in a Document.');
        }
        if (!dialogPolyfill.dm.pushDialog(this)) {
          throw new Error('Failed to execute \'showModal\' on dialog: There are too many open modal dialogs.');
        }
  
        if (createsStackingContext(this.dialog_.parentElement)) {
          console.warn('A dialog is being shown inside a stacking context. ' +
              'This may cause it to be unusable. For more information, see this link: ' +
              'https://github.com/GoogleChrome/dialog-polyfill/#stacking-context');
        }
  
        this.setOpen(true);
        this.openAsModal_ = true;
  
        // Optionally center vertically, relative to the current viewport.
        if (dialogPolyfill.needsCentering(this.dialog_)) {
          dialogPolyfill.reposition(this.dialog_);
          this.replacedStyleTop_ = true;
        } else {
          this.replacedStyleTop_ = false;
        }
  
        // Insert backdrop.
        this.dialog_.parentNode.insertBefore(this.backdrop_, this.dialog_.nextSibling);
  
        // Focus on whatever inside the dialog.
        this.focus_();
      },
  
      /**
       * Closes this HTMLDialogElement. This is optional vs clearing the open
       * attribute, however this fires a 'close' event.
       *
       * @param {string=} opt_returnValue to use as the returnValue
       */
      close: function(opt_returnValue) {
        if (!this.dialog_.hasAttribute('open')) {
          throw new Error('Failed to execute \'close\' on dialog: The element does not have an \'open\' attribute, and therefore cannot be closed.');
        }
        this.setOpen(false);
  
        // Leave returnValue untouched in case it was set directly on the element
        if (opt_returnValue !== undefined) {
          this.dialog_.returnValue = opt_returnValue;
        }
  
        // Triggering "close" event for any attached listeners on the <dialog>.
        var closeEvent = new supportCustomEvent('close', {
          bubbles: false,
          cancelable: false
        });
        this.dialog_.dispatchEvent(closeEvent);
      }
  
    };
  
    var dialogPolyfill = {};
  
    dialogPolyfill.reposition = function(element) {
      var scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
      var topValue = scrollTop + (window.innerHeight - element.offsetHeight) / 2;
      element.style.top = Math.max(scrollTop, topValue) + 'px';
    };
  
    dialogPolyfill.isInlinePositionSetByStylesheet = function(element) {
      for (var i = 0; i < document.styleSheets.length; ++i) {
        var styleSheet = document.styleSheets[i];
        var cssRules = null;
        // Some browsers throw on cssRules.
        try {
          cssRules = styleSheet.cssRules;
        } catch (e) {}
        if (!cssRules) { continue; }
        for (var j = 0; j < cssRules.length; ++j) {
          var rule = cssRules[j];
          var selectedNodes = null;
          // Ignore errors on invalid selector texts.
          try {
            selectedNodes = document.querySelectorAll(rule.selectorText);
          } catch(e) {}
          if (!selectedNodes || !inNodeList(selectedNodes, element)) {
            continue;
          }
          var cssTop = rule.style.getPropertyValue('top');
          var cssBottom = rule.style.getPropertyValue('bottom');
          if ((cssTop && cssTop !== 'auto') || (cssBottom && cssBottom !== 'auto')) {
            return true;
          }
        }
      }
      return false;
    };
  
    dialogPolyfill.needsCentering = function(dialog) {
      var computedStyle = window.getComputedStyle(dialog);
      if (computedStyle.position !== 'absolute') {
        return false;
      }
  
      // We must determine whether the top/bottom specified value is non-auto.  In
      // WebKit/Blink, checking computedStyle.top == 'auto' is sufficient, but
      // Firefox returns the used value. So we do this crazy thing instead: check
      // the inline style and then go through CSS rules.
      if ((dialog.style.top !== 'auto' && dialog.style.top !== '') ||
          (dialog.style.bottom !== 'auto' && dialog.style.bottom !== '')) {
        return false;
      }
      return !dialogPolyfill.isInlinePositionSetByStylesheet(dialog);
    };
  
    /**
     * @param {!Element} element to force upgrade
     */
    dialogPolyfill.forceRegisterDialog = function(element) {
      if (window.HTMLDialogElement || element.showModal) {
        console.warn('This browser already supports <dialog>, the polyfill ' +
            'may not work correctly', element);
      }
      if (element.localName !== 'dialog') {
        throw new Error('Failed to register dialog: The element is not a dialog.');
      }
      new dialogPolyfillInfo(/** @type {!HTMLDialogElement} */ (element));
    };
  
    /**
     * @param {!Element} element to upgrade, if necessary
     */
    dialogPolyfill.registerDialog = function(element) {
      if (!element.showModal) {
        dialogPolyfill.forceRegisterDialog(element);
      }
    };
  
    /**
     * @constructor
     */
    dialogPolyfill.DialogManager = function() {
      /** @type {!Array<!dialogPolyfillInfo>} */
      this.pendingDialogStack = [];
  
      var checkDOM = this.checkDOM_.bind(this);
  
      // The overlay is used to simulate how a modal dialog blocks the document.
      // The blocking dialog is positioned on top of the overlay, and the rest of
      // the dialogs on the pending dialog stack are positioned below it. In the
      // actual implementation, the modal dialog stacking is controlled by the
      // top layer, where z-index has no effect.
      this.overlay = document.createElement('div');
      this.overlay.className = '_dialog_overlay';
      this.overlay.addEventListener('click', function(e) {
        this.forwardTab_ = undefined;
        e.stopPropagation();
        checkDOM([]);  // sanity-check DOM
      }.bind(this));
  
      this.handleKey_ = this.handleKey_.bind(this);
      this.handleFocus_ = this.handleFocus_.bind(this);
  
      this.zIndexLow_ = 100000;
      this.zIndexHigh_ = 100000 + 150;
  
      this.forwardTab_ = undefined;
  
      if ('MutationObserver' in window) {
        this.mo_ = new MutationObserver(function(records) {
          var removed = [];
          records.forEach(function(rec) {
            for (var i = 0, c; c = rec.removedNodes[i]; ++i) {
              if (!(c instanceof Element)) {
                continue;
              } else if (c.localName === 'dialog') {
                removed.push(c);
              }
              removed = removed.concat(c.querySelectorAll('dialog'));
            }
          });
          removed.length && checkDOM(removed);
        });
      }
    };
  
    /**
     * Called on the first modal dialog being shown. Adds the overlay and related
     * handlers.
     */
    dialogPolyfill.DialogManager.prototype.blockDocument = function() {
      document.documentElement.addEventListener('focus', this.handleFocus_, true);
      document.addEventListener('keydown', this.handleKey_);
      this.mo_ && this.mo_.observe(document, {childList: true, subtree: true});
    };
  
    /**
     * Called on the first modal dialog being removed, i.e., when no more modal
     * dialogs are visible.
     */
    dialogPolyfill.DialogManager.prototype.unblockDocument = function() {
      document.documentElement.removeEventListener('focus', this.handleFocus_, true);
      document.removeEventListener('keydown', this.handleKey_);
      this.mo_ && this.mo_.disconnect();
    };
  
    /**
     * Updates the stacking of all known dialogs.
     */
    dialogPolyfill.DialogManager.prototype.updateStacking = function() {
      var zIndex = this.zIndexHigh_;
  
      for (var i = 0, dpi; dpi = this.pendingDialogStack[i]; ++i) {
        dpi.updateZIndex(--zIndex, --zIndex);
        if (i === 0) {
          this.overlay.style.zIndex = --zIndex;
        }
      }
  
      // Make the overlay a sibling of the dialog itself.
      var last = this.pendingDialogStack[0];
      if (last) {
        var p = last.dialog.parentNode || document.body;
        p.appendChild(this.overlay);
      } else if (this.overlay.parentNode) {
        this.overlay.parentNode.removeChild(this.overlay);
      }
    };
  
    /**
     * @param {Element} candidate to check if contained or is the top-most modal dialog
     * @return {boolean} whether candidate is contained in top dialog
     */
    dialogPolyfill.DialogManager.prototype.containedByTopDialog_ = function(candidate) {
      while (candidate = findNearestDialog(candidate)) {
        for (var i = 0, dpi; dpi = this.pendingDialogStack[i]; ++i) {
          if (dpi.dialog === candidate) {
            return i === 0;  // only valid if top-most
          }
        }
        candidate = candidate.parentElement;
      }
      return false;
    };
  
    dialogPolyfill.DialogManager.prototype.handleFocus_ = function(event) {
      if (this.containedByTopDialog_(event.target)) { return; }
  
      event.preventDefault();
      event.stopPropagation();
      safeBlur(/** @type {Element} */ (event.target));
  
      if (this.forwardTab_ === undefined) { return; }  // move focus only from a tab key
  
      var dpi = this.pendingDialogStack[0];
      var dialog = dpi.dialog;
      var position = dialog.compareDocumentPosition(event.target);
      if (position & Node.DOCUMENT_POSITION_PRECEDING) {
        if (this.forwardTab_) {  // forward
          dpi.focus_();
        } else {  // backwards
          document.documentElement.focus();
        }
      } else {
        // TODO: Focus after the dialog, is ignored.
      }
  
      return false;
    };
  
    dialogPolyfill.DialogManager.prototype.handleKey_ = function(event) {
      this.forwardTab_ = undefined;
      if (event.keyCode === 27) {
        event.preventDefault();
        event.stopPropagation();
        var cancelEvent = new supportCustomEvent('cancel', {
          bubbles: false,
          cancelable: true
        });
        var dpi = this.pendingDialogStack[0];
        if (dpi && dpi.dialog.dispatchEvent(cancelEvent)) {
          dpi.dialog.close();
        }
      } else if (event.keyCode === 9) {
        this.forwardTab_ = !event.shiftKey;
      }
    };
  
    /**
     * Finds and downgrades any known modal dialogs that are no longer displayed. Dialogs that are
     * removed and immediately readded don't stay modal, they become normal.
     *
     * @param {!Array<!HTMLDialogElement>} removed that have definitely been removed
     */
    dialogPolyfill.DialogManager.prototype.checkDOM_ = function(removed) {
      // This operates on a clone because it may cause it to change. Each change also calls
      // updateStacking, which only actually needs to happen once. But who removes many modal dialogs
      // at a time?!
      var clone = this.pendingDialogStack.slice();
      clone.forEach(function(dpi) {
        if (removed.indexOf(dpi.dialog) !== -1) {
          dpi.downgradeModal();
        } else {
          dpi.maybeHideModal();
        }
      });
    };
  
    /**
     * @param {!dialogPolyfillInfo} dpi
     * @return {boolean} whether the dialog was allowed
     */
    dialogPolyfill.DialogManager.prototype.pushDialog = function(dpi) {
      var allowed = (this.zIndexHigh_ - this.zIndexLow_) / 2 - 1;
      if (this.pendingDialogStack.length >= allowed) {
        return false;
      }
      if (this.pendingDialogStack.unshift(dpi) === 1) {
        this.blockDocument();
      }
      this.updateStacking();
      return true;
    };
  
    /**
     * @param {!dialogPolyfillInfo} dpi
     */
    dialogPolyfill.DialogManager.prototype.removeDialog = function(dpi) {
      var index = this.pendingDialogStack.indexOf(dpi);
      if (index === -1) { return; }
  
      this.pendingDialogStack.splice(index, 1);
      if (this.pendingDialogStack.length === 0) {
        this.unblockDocument();
      }
      this.updateStacking();
    };
  
    dialogPolyfill.dm = new dialogPolyfill.DialogManager();
    dialogPolyfill.formSubmitter = null;
    dialogPolyfill.useValue = null;
  
    /**
     * Installs global handlers, such as click listers and native method overrides. These are needed
     * even if a no dialog is registered, as they deal with <form method="dialog">.
     */
    if (window.HTMLDialogElement === undefined) {
  
      /**
       * If HTMLFormElement translates method="DIALOG" into 'get', then replace the descriptor with
       * one that returns the correct value.
       */
      var testForm = document.createElement('form');
      testForm.setAttribute('method', 'dialog');
      if (testForm.method !== 'dialog') {
        var methodDescriptor = Object.getOwnPropertyDescriptor(HTMLFormElement.prototype, 'method');
        if (methodDescriptor) {
          // nb. Some older iOS and older PhantomJS fail to return the descriptor. Don't do anything
          // and don't bother to update the element.
          var realGet = methodDescriptor.get;
          methodDescriptor.get = function() {
            if (isFormMethodDialog(this)) {
              return 'dialog';
            }
            return realGet.call(this);
          };
          var realSet = methodDescriptor.set;
          methodDescriptor.set = function(v) {
            if (typeof v === 'string' && v.toLowerCase() === 'dialog') {
              return this.setAttribute('method', v);
            }
            return realSet.call(this, v);
          };
          Object.defineProperty(HTMLFormElement.prototype, 'method', methodDescriptor);
        }
      }
  
      /**
       * Global 'click' handler, to capture the <input type="submit"> or <button> element which has
       * submitted a <form method="dialog">. Needed as Safari and others don't report this inside
       * document.activeElement.
       */
      document.addEventListener('click', function(ev) {
        dialogPolyfill.formSubmitter = null;
        dialogPolyfill.useValue = null;
        if (ev.defaultPrevented) { return; }  // e.g. a submit which prevents default submission
  
        var target = /** @type {Element} */ (ev.target);
        if (!target || !isFormMethodDialog(target.form)) { return; }
  
        var valid = (target.type === 'submit' && ['button', 'input'].indexOf(target.localName) > -1);
        if (!valid) {
          if (!(target.localName === 'input' && target.type === 'image')) { return; }
          // this is a <input type="image">, which can submit forms
          dialogPolyfill.useValue = ev.offsetX + ',' + ev.offsetY;
        }
  
        var dialog = findNearestDialog(target);
        if (!dialog) { return; }
  
        dialogPolyfill.formSubmitter = target;
      }, false);
  
      /**
       * Replace the native HTMLFormElement.submit() method, as it won't fire the
       * submit event and give us a chance to respond.
       */
      var nativeFormSubmit = HTMLFormElement.prototype.submit;
      var replacementFormSubmit = function () {
        if (!isFormMethodDialog(this)) {
          return nativeFormSubmit.call(this);
        }
        var dialog = findNearestDialog(this);
        dialog && dialog.close();
      };
      HTMLFormElement.prototype.submit = replacementFormSubmit;
  
      /**
       * Global form 'dialog' method handler. Closes a dialog correctly on submit
       * and possibly sets its return value.
       */
      document.addEventListener('submit', function(ev) {
        var form = /** @type {HTMLFormElement} */ (ev.target);
        if (!isFormMethodDialog(form)) { return; }
        ev.preventDefault();
  
        var dialog = findNearestDialog(form);
        if (!dialog) { return; }
  
        // Forms can only be submitted via .submit() or a click (?), but anyway: sanity-check that
        // the submitter is correct before using its value as .returnValue.
        var s = dialogPolyfill.formSubmitter;
        if (s && s.form === form) {
          dialog.close(dialogPolyfill.useValue || s.value);
        } else {
          dialog.close();
        }
        dialogPolyfill.formSubmitter = null;
      }, true);
    }
  
    dialogPolyfill['forceRegisterDialog'] = dialogPolyfill.forceRegisterDialog;
    dialogPolyfill['registerDialog'] = dialogPolyfill.registerDialog;
  
    if (typeof define === 'function' && 'amd' in define) {
      // AMD support
      define(function() { return dialogPolyfill; });
    } else if (typeof module === 'object' && typeof module['exports'] === 'object') {
      // CommonJS support
      module['exports'] = dialogPolyfill;
    } else {
      // all others
      window['dialogPolyfill'] = dialogPolyfill;
    }
  })();
  /**
 * Updates the passed dialog to retain focus and restore it when the dialog is closed. Won't
 * upgrade a dialog more than once. Supports IE11+ and is a no-op otherwise.
 * @param {!HTMLDialogElement} dialog to upgrade
 */

var registerFocusRestoreDialog = (function() {
    if (!window.WeakMap || !window.MutationObserver) {
      return function() {};
    }
    var registered = new WeakMap();
  
    // store previous focused node centrally
    var previousFocus = null;
    document.addEventListener('focusout', function(ev) {
      previousFocus = ev.target;
    }, true);
  
    return function registerFocusRestoreDialog(dialog) {
      if (dialog.localName !== 'dialog') {
        throw new Error('Failed to upgrade focus on dialog: The element is not a dialog.');
      }
      if (registered.has(dialog)) { return; }
      registered.set(dialog, null);
  
      // replace showModal method directly, to save focus
      var realShowModal = dialog.showModal;
      dialog.showModal = function() {
        var savedFocus = document.activeElement;
        if (savedFocus === document || savedFocus === document.body) {
          // some browsers read activeElement as body
          savedFocus = previousFocus;
        }
        registered.set(dialog, savedFocus);
        realShowModal.call(this);
      };
  
      // watch for 'open' change and clear saved
      var mo = new MutationObserver(function() {
        if (dialog.hasAttribute('open')) {
            accessibility.setKeepFocusInside(dialog, true);
        } else {
            accessibility.setKeepFocusInside(dialog, false);
        }
      });
      mo.observe(dialog, {attributes: true, attributeFilter: ['open']});
  
      // on close, try to focus saved, if possible
      dialog.addEventListener('close', function(ev) {
        if (dialog.hasAttribute('open')) {
          return;  // in native, this fires the frame later
        }
        var savedFocus = registered.get(dialog);
        if (document.contains(savedFocus)) {
          var wasFocus = document.activeElement;
          savedFocus.focus();
          if (document.activeElement !== savedFocus) {
            wasFocus.focus();  // restore focus, we couldn't focus saved
          }
        }
        savedFocus = null;
        registered.set(dialog, null);
      });
  
      // FIXME: If a modal dialog is readded to the page (either remove/add or .appendChild), it will
      // be a non-modal. It will still have its 'close' handler called and try to focus on the saved
      // element.
      //
      // These could basically be solved if 'close' yielded whether it was a modal or non-modal
      // being closed. But it doesn't. It could also be solved by a permanent MutationObserver, as is
      // done inside the polyfill.
    }
  }());