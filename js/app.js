/**
 * Online Registration Portal - Client-side JavaScript & jQuery
 * Handles real-time validation, photo preview, dynamic UI, AJAX submission,
 * and result formatting/printing.
 */

$(document).ready(function () {
  // DOM Elements
  const $form = $('#registrationForm');
  const $submitBtn = $('#submitBtn');
  const $formContainer = $('#formContainer');
  const $resultContainer = $('#resultContainer');
  const $alertBox = $('#alertBox');
  const $photoInput = $('#photoInput');
  const $photoPreview = $('#previewImg');
  const $photoIcon = $('#photoPlaceholderIcon');
  const $sopInput = $('#statementOfPurpose');
  const $charCount = $('#charCount');

  // Regex Patterns
  const patterns = {
    name: /^[a-zA-Z\s\.\']{2,60}$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    phone: /^[+]?[0-9\s\-().]{7,25}$/,
    zip: /^[a-zA-Z0-9\s-]{3,10}$/
  };

  // -------------------------------------------------------------
  // 1. Character Counter for Statement of Purpose
  // -------------------------------------------------------------
  if ($sopInput.length) {
    $sopInput.on('input', function () {
      const currentLength = $(this).val().length;
      $charCount.text(currentLength);
      if (currentLength > 500) {
        $charCount.css('color', 'var(--danger)');
      } else {
        $charCount.css('color', 'var(--text-muted)');
      }
    });
  }

  // -------------------------------------------------------------
  // 2. Photo Upload Live Preview & Validation
  // -------------------------------------------------------------
  $photoInput.on('change', function (e) {
    const file = e.target.files[0];
    const $error = $('#photoError');
    $error.removeClass('visible').text('');

    if (!file) {
      $photoPreview.hide().attr('src', '');
      $photoIcon.show();
      return;
    }

    // Validate size (max 3MB)
    const maxSize = 3 * 1024 * 1024;
    if (file.size > maxSize) {
      $error.text('File size exceeds 3MB limit.').addClass('visible');
      $(this).val('');
      return;
    }

    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!validTypes.includes(file.type)) {
      $error.text('Only JPG, PNG, and WebP images are allowed.').addClass('visible');
      $(this).val('');
      return;
    }

    // Read and preview
    const reader = new FileReader();
    reader.onload = function (event) {
      $photoIcon.hide();
      $photoPreview.attr('src', event.target.result).show();
    };
    reader.readAsDataURL(file);
  });

  // -------------------------------------------------------------
  // 3. Radio & Checkbox Active Styling Toggle
  // -------------------------------------------------------------
  $('input[type="radio"]').on('change', function () {
    const groupName = $(this).attr('name');
    $(`input[name="${groupName}"]`).closest('.radio-label').removeClass('active');
    $(this).closest('.radio-label').addClass('active');
  });

  $('.checkbox-label input[type="checkbox"]').on('change', function () {
    $(this).closest('.checkbox-label').toggleClass('active', this.checked);
  });

  // -------------------------------------------------------------
  // 4. Real-time Input Validation Helpers
  // -------------------------------------------------------------
  function validateField($field, isValid, errorMsg) {
    const $errorEl = $(`#${$field.attr('id')}Error`);
    if (isValid) {
      $field.removeClass('is-invalid').addClass('is-valid');
      if ($errorEl.length) $errorEl.removeClass('visible').text('');
      return true;
    } else {
      $field.removeClass('is-valid').addClass('is-invalid');
      if ($errorEl.length) $errorEl.addClass('visible').text(errorMsg);
      return false;
    }
  }

  // Bind blur/input validation
  $('#fullName').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.name.test(val), 'Please enter a valid full name (letters only, min 2 chars).');
  });

  $('#email').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.email.test(val), 'Please enter a valid email address.');
  });

  $('#phone').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.phone.test(val), 'Please enter a valid phone number (at least 10 digits).');
  });

  $('#dob').on('change blur', function () {
    const val = $(this).val();
    if (!val) {
      validateField($(this), false, 'Please select your date of birth.');
      return;
    }
    const dob = new Date(val);
    const today = new Date();
    const age = today.getFullYear() - dob.getFullYear();
    const isValidAge = age >= 14 && age <= 100;
    validateField($(this), isValidAge, 'Applicant must be at least 14 years old.');
  });

  $('#course').on('change', function () {
    const val = $(this).val();
    validateField($(this), val !== '', 'Please select an academic course/program.');
  });

  $('#city').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), val.length >= 2, 'Please enter your city.');
  });

  $('#country').on('change', function () {
    const val = $(this).val();
    validateField($(this), val !== '', 'Please select your country.');
  });

  // -------------------------------------------------------------
  // 5. Full Form Validation Before Submission
  // -------------------------------------------------------------
  function validateForm() {
    let isValid = true;
    let $firstInvalid = null;

    // Validate Name
    const nameVal = $('#fullName').val().trim();
    if (!validateField($('#fullName'), patterns.name.test(nameVal), 'Full Name is required (letters only).')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#fullName');
    }

    // Validate Email
    const emailVal = $('#email').val().trim();
    if (!validateField($('#email'), patterns.email.test(emailVal), 'Valid email address is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#email');
    }

    // Validate Phone
    const phoneVal = $('#phone').val().trim();
    if (!validateField($('#phone'), patterns.phone.test(phoneVal), 'Valid phone number is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#phone');
    }

    // Validate DOB
    const dobVal = $('#dob').val();
    if (!dobVal) {
      validateField($('#dob'), false, 'Date of birth is required.');
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#dob');
    }

    // Validate Gender
    const genderVal = $('input[name="gender"]:checked').val();
    const $genderErr = $('#genderError');
    if (!genderVal) {
      $genderErr.text('Please select your gender.').addClass('visible');
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('input[name="gender"]').first();
    } else {
      $genderErr.removeClass('visible');
    }

    // Validate Course
    const courseVal = $('#course').val();
    if (!validateField($('#course'), !!courseVal, 'Please select a program/course.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#course');
    }

    // Validate City
    const cityVal = $('#city').val().trim();
    if (!validateField($('#city'), cityVal.length >= 2, 'City is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#city');
    }

    // Validate Country
    const countryVal = $('#country').val();
    if (!validateField($('#country'), !!countryVal, 'Country is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#country');
    }

    // Validate Terms Checkbox
    const termsChecked = $('#terms').is(':checked');
    const $termsErr = $('#termsError');
    if (!termsChecked) {
      $termsErr.text('You must agree to the declarations and terms to proceed.').addClass('visible');
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#terms');
    } else {
      $termsErr.removeClass('visible');
    }

    if ($firstInvalid) {
      $('html, body').animate(
        {
          scrollTop: $firstInvalid.offset().top - 120
        },
        400
      );
      $firstInvalid.focus();
    }

    return isValid;
  }

  // -------------------------------------------------------------
  // 6. Form Submission via AJAX (with jQuery)
  // -------------------------------------------------------------
  $form.on('submit', function (e) {
    e.preventDefault();

    // Reset alert
    $alertBox.hide().empty();

    // Validate all fields
    if (!validateForm()) {
      showAlert('Please correct the highlighted errors before submitting.', 'danger');
      return false;
    }

    // Show loading state
    $submitBtn.prop('disabled', true).html('<span class="spinner"></span> Processing Registration...');

    // Prepare FormData (supports file uploads seamlessly)
    const formData = new FormData(this);

    // Perform jQuery AJAX POST
    $.ajax({
      url: 'process.php',
      type: 'POST',
      data: formData,
      processData: false, // Required for FormData
      contentType: false, // Required for FormData
      dataType: 'json',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function (response) {
        if (response.success) {
          // Success! Render styled output
          renderConfirmationView(response.data, response.html);
        } else {
          // Server returned validation or processing errors
          const errMsg = response.message || 'Registration failed. Please check your information.';
          showAlert(errMsg, 'danger');
          $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application');
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error, xhr.responseText);
        let errorMsg = 'An unexpected server error occurred. Please try again.';
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.message) errorMsg = res.message;
        } catch (err) {
          // Not json
        }
        showAlert(errorMsg, 'danger');
        $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application');
      }
    });
  });

  // -------------------------------------------------------------
  // 7. Render Formatted Output on Success
  // -------------------------------------------------------------
  function renderConfirmationView(data, serverHtml) {
    // Hide the input form card smoothly
    $formContainer.slideUp(350, function () {
      if (serverHtml) {
        // If server provided pre-rendered formatted HTML, insert it
        $resultContainer.html(serverHtml);
      } else {
        // Fallback: Populate client template with received data
        populateClientCard(data);
      }

      // Show formatted confirmation document
      $resultContainer.fadeIn(400);

      // Smooth scroll to top of document sheet
      $('html, body').animate(
        {
          scrollTop: $resultContainer.offset().top - 30
        },
        500
      );

      // Bind events on new buttons
      bindResultEvents();
    });
  }

  // -------------------------------------------------------------
  // 8. Event Handlers for Confirmation Actions (Print / New Form)
  // -------------------------------------------------------------
  function bindResultEvents() {
    // Print / Save to PDF Button
    $(document).off('click', '#btnPrint').on('click', '#btnPrint', function () {
      window.print();
    });

    // Reset and Submit Another Application Button
    $(document).off('click', '#btnNewForm').on('click', '#btnNewForm', function () {
      $form[0].reset();
      $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
      $('.radio-label, .checkbox-label').removeClass('active');
      $photoPreview.hide().attr('src', '');
      $photoIcon.show();
      $charCount.text('0');

      $resultContainer.fadeOut(300, function () {
        $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application');
        $formContainer.slideDown(350);
        $('html, body').animate({ scrollTop: 0 }, 400);
      });
    });
  }

  // Also bind initial buttons in case of server-rendered page load
  bindResultEvents();

  // Helper alert display
  function showAlert(message, type) {
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    $alertBox
      .removeClass('alert-success alert-danger')
      .addClass(`alert-${type}`)
      .html(`<i class="fas ${iconClass}"></i> <div>${message}</div>`)
      .fadeIn();

    $('html, body').animate(
      {
        scrollTop: $alertBox.offset().top - 100
      },
      300
    );
  }
});
