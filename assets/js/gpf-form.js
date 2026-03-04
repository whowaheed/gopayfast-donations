document.addEventListener('DOMContentLoaded', function () {
  // --- START: Pre-fill logic from URL ---
  const urlParams = new URLSearchParams(window.location.search);
  const amount = urlParams.get('amount');
  const fullName = urlParams.get('full-name');
  const email = urlParams.get('email');

  if (amount) {
    const customAmountInput = document.getElementById('custom-amount');
    if (customAmountInput) customAmountInput.value = amount;

    // Check if the amount matches a preset button and activate it
    const amountButtons = document.querySelectorAll('#amount-options .amount-btn');
    amountButtons.forEach((btn) => {
      if (btn.getAttribute('data-amount') === amount) {
        btn.classList.add('active');
      }
    });
  }

  if (fullName) {
    const fullNameInput = document.getElementById('full-name');
    if (fullNameInput) fullNameInput.value = fullName;
  }

  if (email) {
    const emailInput = document.getElementById('email');
    if (emailInput) emailInput.value = email;
  }
  // --- END: Pre-fill logic ---

  // --- Original donation form JS ---
  const amountOptions = document.getElementById('amount-options');

  // Exit if the element doesn't exist on this page
  if (!amountOptions) {
    return;
  }

  const amountButtons = amountOptions.querySelectorAll('.amount-btn');
  const customAmountInput = document.getElementById('custom-amount');

  amountButtons.forEach((button) => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      amountButtons.forEach((btn) => btn.classList.remove('active'));
      this.classList.add('active');
      customAmountInput.value = this.getAttribute('data-amount');
    });
  });

  customAmountInput.addEventListener('input', function () {
    amountButtons.forEach((btn) => btn.classList.remove('active'));
  });
});
