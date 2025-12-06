// script.js
// Client-side validation + lightweight jQuery enhancements

$(function () {
  const $form = $('#regForm');
  const $submit = $('#submitBtn');

  function showError(field, message) {
    const $el = $(`[data-for="${field}"]`);
    $el.text(message);
    $(`#${field}`).addClass('input-error');
  }
  function clearError(field) {
    $(`[data-for="${field}"]`).text('');
    $(`#${field}`).removeClass('input-error');
  }

  function validate() {
    let valid = true;
    // Full name
    const name = $('#fullname').val().trim();
    if (!name) {
      showError('fullname', 'Full name is required.');
      valid = false;
    } else if (name.length < 3) {
      showError('fullname', 'Enter a valid name (min 3 chars).');
      valid = false;
    } else clearError('fullname');

    // Email
    const email = $('#email').val().trim();
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
      showError('email', 'Email is required.');
      valid = false;
    } else if (!emailRe.test(email)) {
      showError('email', 'Enter a valid email.');
      valid = false;
    } else clearError('email');

    // Phone
    const phone = $('#phone').val().replace(/\D/g, '');
    const phoneRe = /^[6-9]\d{9}$/; // common Indian mobile pattern
    if (!phone) {
      showError('phone', 'Mobile number is required.');
      valid = false;
    } else if (!phoneRe.test(phone)) {
      showError('phone', 'Enter a valid 10-digit mobile starting with 6-9.');
      valid = false;
    } else clearError('phone');

    // Course
    const course = $('#course').val();
    if (!course) {
      showError('course', 'Please select a course.');
      valid = false;
    } else clearError('course');

    return valid;
  }

  // Clear specific field error on input
  $('input, select, textarea').on('input change', function () {
    const id = $(this).attr('id');
    if (id) clearError(id);
  });

  // Submit handler with final validation
  $form.on('submit', function (e) {
    if (!validate()) {
      e.preventDefault();
      // add a little shake for invalid form
      $form.css('animation', 'shake .45s');
      setTimeout(() => $form.css('animation', ''), 460);
      return false;
    }
    // allow submit to server (no ajax) - will be handled by submit.php
    $submit.prop('disabled', true).text('Submitting...');
  });

});
