(function () {
  document.addEventListener('click', function (event) {
    var btn = event.target.closest('.delete-training');
    if (!btn) {
      return;
    }
    if (!window.confirm('هل تريد حذف التدريب؟')) {
      event.preventDefault();
    }
  });
})();
