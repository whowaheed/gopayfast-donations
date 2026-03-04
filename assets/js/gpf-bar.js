document.addEventListener('DOMContentLoaded', function () {
  const amountOptions = document.getElementById('amount-options');

  // Exit if the element doesn't exist on this page
  if (!amountOptions) {
    return;
  }

  const amountButtons = amountOptions.querySelectorAll('.amount-btn-bar');
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
