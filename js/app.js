/**
 * Online Admission Portal - Indian Layout & Validation Scripts
 * Implements real-time Indian mobile (+91) & 6-digit PIN validation,
 * live candidate preview card sync, photo preview, AJAX submit, and official printable slip.
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
  const $photoPlaceholderText = $('#photoPlaceholderText');
  const $sopInput = $('#statementOfPurpose');
  const $charCount = $('#charCount');

  // Live Summary Elements
  const $liveName = $('#liveName');
  const $liveEmail = $('#liveEmail');
  const $livePhone = $('#livePhone');
  const $liveCourse = $('#liveCourse');
  const $liveLocation = $('#liveLocation');

  // -------------------------------------------------------------
  // 1. Validation Patterns (Indian Specifications)
  // -------------------------------------------------------------
  const patterns = {
    name: /^[a-zA-Z\s\.\']{2,60}$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    pinCode: /^[1-9][0-9]{5}$/ // Standard Indian 6-Digit Postal PIN Code
  };

  // Helper to validate Indian Mobile Number (10 digits starting with 6,7,8,9)
  function isValidIndianMobile(phoneStr) {
    if (!phoneStr) return false;
    // Extract only digits
    const digits = phoneStr.replace(/\D/g, '');
    if (digits.length === 10) {
      return /^[6789]\d{9}$/.test(digits);
    } else if (digits.length === 12 && digits.startsWith('91')) {
      return /^[6789]\d{9}$/.test(digits.substring(2));
    }
    return false;
  }

  // -------------------------------------------------------------
  // 2. Live Synchronization with Sidebar Preview Card
  // -------------------------------------------------------------
  function updateLiveSummary() {
    const name = $('#fullName').val().trim() || 'Aarav Sharma';
    const email = $('#email').val().trim() || 'aarav.sharma@example.in';
    const rawPhone = $('#phone').val().trim() || '98765 43210';
    const course = $('#course').val() || 'B.Tech in Computer Science & Engineering';
    const city = $('#city').val().trim() || 'Bengaluru';
    const state = $('#state').val() || 'Karnataka';
    const pin = $('#zipCode').val().trim() || '560001';

    $liveName.text(name);
    $liveEmail.text(email);
    $livePhone.text('+91 ' + rawPhone.replace(/^\+91\s*/, ''));
    $liveCourse.text(course);
    $liveLocation.text(`${city}, ${state} - ${pin}`);
  }

  // Listen on input/change events to update summary
  $('#fullName, #email, #phone, #course, #city, #state, #zipCode').on('input change', updateLiveSummary);
  updateLiveSummary(); // Initial sync

  // -------------------------------------------------------------
  // 3. Statement of Purpose Character Counter
  // -------------------------------------------------------------
  if ($sopInput.length) {
    const initLen = $sopInput.val().length;
    $charCount.text(initLen);

    $sopInput.on('input', function () {
      const len = $(this).val().length;
      $charCount.text(len);
      $charCount.css('color', len > 500 ? 'var(--danger)' : 'var(--text-muted)');
    });
  }

  // -------------------------------------------------------------
  // 4. Photo Upload Live Preview & Size Validation
  // -------------------------------------------------------------
  $photoInput.on('change', function (e) {
    const file = e.target.files[0];
    const $error = $('#photoError');
    $error.removeClass('visible').text('');

    if (!file) {
      $photoPreview.hide().attr('src', '');
      $photoIcon.show();
      $photoPlaceholderText.show();
      return;
    }

    // Validate size (max 3MB)
    if (file.size > 3 * 1024 * 1024) {
      $error.text('File size exceeds 3MB limit.').addClass('visible');
      $(this).val('');
      return;
    }

    // Validate image format
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!validTypes.includes(file.type)) {
      $error.text('Only JPG, PNG, or WebP passport photographs are accepted.').addClass('visible');
      $(this).val('');
      return;
    }

    // Read and display preview
    const reader = new FileReader();
    reader.onload = function (event) {
      $photoIcon.hide();
      $photoPlaceholderText.hide();
      $photoPreview.attr('src', event.target.result).show();
    };
    reader.readAsDataURL(file);
  });

  // -------------------------------------------------------------
  // 5. Radio & Checkbox Active Styling Toggles
  // -------------------------------------------------------------
  $('input[type="radio"]').on('change', function () {
    const group = $(this).attr('name');
    $(`input[name="${group}"]`).closest('.radio-label').removeClass('active');
    $(this).closest('.radio-label').addClass('active');
  });

  $('.checkbox-label input[type="checkbox"]').on('change', function () {
    $(this).closest('.checkbox-label').toggleClass('active', this.checked);
  });

  // -------------------------------------------------------------
  // 6. Real-time Field Validation Helpers
  // -------------------------------------------------------------
  function validateField($field, isValid, errorMsg) {
    const id = $field.attr('id');
    const $errorEl = $(`#${id}Error`);
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

  $('#fullName').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.name.test(val), 'Please enter candidate legal full name (letters only, min 2 chars).');
  });

  $('#email').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.email.test(val), 'Please enter a valid email address.');
  });

  $('#phone').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), isValidIndianMobile(val), 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
  });

  $('#dob').on('change blur', function () {
    const val = $(this).val();
    if (!val) {
      validateField($(this), false, 'Date of birth is required.');
      return;
    }
    const dob = new Date(val);
    const today = new Date();
    const age = today.getFullYear() - dob.getFullYear();
    validateField($(this), age >= 14 && age <= 100, 'Candidate must be at least 14 years of age.');
  });

  $('#address').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), val.length >= 5, 'Please provide full street / house address (min 5 chars).');
  });

  $('#city').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), val.length >= 2, 'City / District is required.');
  });

  $('#state').on('change blur', function () {
    const val = $(this).val();
    validateField($(this), !!val, 'Please select your State or Union Territory.');
  });

  $('#zipCode').on('input blur', function () {
    const val = $(this).val().trim();
    validateField($(this), patterns.pinCode.test(val), 'Please enter a valid 6-digit Indian PIN Code (e.g. 560001).');
  });

  // -------------------------------------------------------------
  // 7. Full Form Validation Prior to Submission
  // -------------------------------------------------------------
  function validateEntireForm() {
    let isValid = true;
    let $firstInvalid = null;

    // Name
    const nameVal = $('#fullName').val().trim();
    if (!validateField($('#fullName'), patterns.name.test(nameVal), 'Full Name is required (letters only).')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#fullName');
    }

    // Email
    const emailVal = $('#email').val().trim();
    if (!validateField($('#email'), patterns.email.test(emailVal), 'Valid email address is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#email');
    }

    // Phone
    const phoneVal = $('#phone').val().trim();
    if (!validateField($('#phone'), isValidIndianMobile(phoneVal), 'Valid 10-digit Indian mobile number is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#phone');
    }

    // DOB
    const dobVal = $('#dob').val();
    if (!dobVal) {
      validateField($('#dob'), false, 'Date of birth is required.');
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#dob');
    }

    // Address
    const addressVal = $('#address').val().trim();
    if (!validateField($('#address'), addressVal.length >= 5, 'Permanent street address is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#address');
    }

    // City
    const cityVal = $('#city').val().trim();
    if (!validateField($('#city'), cityVal.length >= 2, 'City / District is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#city');
    }

    // State
    const stateVal = $('#state').val();
    if (!validateField($('#state'), !!stateVal, 'State / Union Territory is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#state');
    }

    // PIN Code
    const pinVal = $('#zipCode').val().trim();
    if (!validateField($('#zipCode'), patterns.pinCode.test(pinVal), 'Valid 6-digit Indian PIN Code is required.')) {
      isValid = false;
      if (!$firstInvalid) $firstInvalid = $('#zipCode');
    }

    // Terms
    const termsChecked = $('#terms').is(':checked');
    const $termsErr = $('#termsError');
    if (!termsChecked) {
      $termsErr.text('Candidate must accept the declaration to submit the application.').addClass('visible');
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
        350
      );
      $firstInvalid.focus();
    }

    return isValid;
  }

  // -------------------------------------------------------------
  // 8. Form Submission via AJAX (with jQuery)
  // -------------------------------------------------------------
  $form.on('submit', function (e) {
    e.preventDefault();
    $alertBox.hide().empty();

    if (!validateEntireForm()) {
      showAlert('Please resolve the highlighted validation errors before submitting.', 'danger');
      return false;
    }

    // Show loading spinner
    $submitBtn.prop('disabled', true).html('<span class="spinner"></span> Processing Registration...');

    // FormData object (supports photo upload)
    const formData = new FormData(this);

    // Make AJAX POST request
    $.ajax({
      url: 'process.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function (response) {
        if (response.success) {
          // Success! Render official formatted result
          renderOfficialSlip(response.html);
        } else {
          showAlert(response.message || 'Registration could not be processed. Please verify your details.', 'danger');
          $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application Form');
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error, xhr.responseText);
        let errorMsg = 'An unexpected server error occurred. Please try again.';
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.message) errorMsg = res.message;
        } catch (err) {}
        showAlert(errorMsg, 'danger');
        $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application Form');
      }
    });
  });

  // -------------------------------------------------------------
  // 9. Render Official Formatted Indian Result Slip
  // -------------------------------------------------------------
  function renderOfficialSlip(serverHtml) {
    $formContainer.slideUp(350, function () {
      $resultContainer.html(serverHtml).fadeIn(400);

      $('html, body').animate(
        {
          scrollTop: $resultContainer.offset().top - 20
        },
        450
      );

      bindSlipActions();
    });
  }

  function bindSlipActions() {
    // Print / Save to PDF
    $(document).off('click', '#btnPrint').on('click', '#btnPrint', function () {
      window.print();
    });

    // Reset & Submit Another Application
    $(document).off('click', '#btnNewForm').on('click', '#btnNewForm', function () {
      $form[0].reset();
      $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
      $photoPreview.hide().attr('src', '');
      $photoIcon.show();
      $photoPlaceholderText.show();
      updateLiveSummary();

      $resultContainer.fadeOut(300, function () {
        $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Application Form');
        $formContainer.slideDown(350);
        $('html, body').animate({ scrollTop: 0 }, 350);
      });
    });
  }

  bindSlipActions();

  // Helper Alert
  function showAlert(message, type) {
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    $alertBox
      .removeClass('alert-success alert-danger')
      .addClass(`alert-${type}`)
      .html(`<i class="fas ${icon}"></i> <div>${message}</div>`)
      .fadeIn();

    $('html, body').animate(
      {
        scrollTop: $alertBox.offset().top - 90
      },
      300
    );
  }
});
