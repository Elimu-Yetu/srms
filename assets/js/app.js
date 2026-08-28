/* Elimu Yetu SRMS — small, dependency-free interactions. */
(function () {
  'use strict';

  // Mobile navigation drawer
  var burger = document.getElementById('burger');
  var scrim  = document.getElementById('scrim');
  function closeNav() {
    document.body.classList.remove('nav-open');
    if (burger) burger.setAttribute('aria-expanded', 'false');
  }
  if (burger) {
    burger.addEventListener('click', function () {
      var open = document.body.classList.toggle('nav-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  if (scrim) scrim.addEventListener('click', closeNav);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });
  window.addEventListener('resize', function () { if (window.innerWidth > 860) closeNav(); });

  // Confirm destructive actions declared with data-confirm
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (el && !window.confirm(el.getAttribute('data-confirm'))) e.preventDefault();
  });

  // Attendance: set every unmarked student at once
  document.querySelectorAll('[data-markall]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var v = btn.getAttribute('data-markall');
      document.querySelectorAll('input[type=radio][value="' + v + '"]').forEach(function (r) { r.checked = true; });
    });
  });

  // Filter a table live, client side (no round trip on small lists)
  document.querySelectorAll('[data-filter]').forEach(function (input) {
    var table = document.querySelector(input.getAttribute('data-filter'));
    if (!table) return;
    input.addEventListener('input', function () {
      var term = input.value.toLowerCase().trim();
      table.querySelectorAll('tbody tr').forEach(function (tr) {
        tr.style.display = !term || tr.textContent.toLowerCase().indexOf(term) > -1 ? '' : 'none';
      });
    });
  });

  // Submit a filter form when a select changes
  document.querySelectorAll('[data-autosubmit]').forEach(function (sel) {
    sel.addEventListener('change', function () { sel.form && sel.form.submit(); });
  });

  // Keep totals honest while typing kitchen counts
  var kitchenForm = document.getElementById('kitchen-form');
  if (kitchenForm) {
    var out = document.getElementById('kitchen-live');
    var recalc = function () {
      var g = function (n) {
        var el = kitchenForm.elements[n];
        return el ? (parseInt(el.value, 10) || 0) : 0;
      };
      var tt = g('tea_teachers'), ts = g('tea_students');
      var mt = g('meals_teachers'), ms = g('meals_students');
      var tea = tt + ts, meal = mt + ms;
      var teachers = tt + mt, students = ts + ms, all = tea + meal;
      if (!out) return;
      var pc = function (n) { return all ? ((n / all) * 100).toFixed(1) : 0; };
      out.innerHTML =
        '<div class="stat" style="box-shadow:none;margin-bottom:10px">' +
          '<div class="stat__label">Servings today</div>' +
          '<div class="stat__value">' + all + '</div>' +
          '<div class="stat__note">' + tea + ' cups of tea · ' + meal + ' plates of food</div>' +
        '</div>' +
        '<div class="segbar">' +
          '<span class="seg-teachers" style="width:' + pc(teachers) + '%"></span>' +
          '<span class="seg-students" style="width:' + pc(students) + '%"></span>' +
        '</div>' +
        '<div class="seglegend">' +
          '<span><i class="seg-teachers"></i>Teachers ' + teachers + '</span>' +
          '<span><i class="seg-students"></i>Students ' + students + '</span>' +
        '</div>' +
        '<dl class="dl" style="margin-top:12px">' +
          '<dt>Tea</dt><dd class="mono">' + tt + ' teachers · ' + ts + ' students</dd>' +
          '<dt>Meals</dt><dd class="mono">' + mt + ' teachers · ' + ms + ' students</dd>' +
        '</dl>';
    };
    kitchenForm.addEventListener('input', recalc);
    recalc();
  }
})();
