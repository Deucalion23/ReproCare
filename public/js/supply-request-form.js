(() => {
    'use strict';
    const category = document.getElementById('supply-category');
    if (!category) return;
    const catalog = JSON.parse(document.getElementById('supply-catalog').textContent);
    const item = document.getElementById('supply-name');
    const unit = document.getElementById('supply-unit');
    const other = document.getElementById('other-supply-field');
    const otherInput = document.getElementById('supply-name-other');
    const populate = (select, values, placeholder, selected) => {
        select.replaceChildren(new Option(placeholder, ''));
        values.forEach(([value, label]) => select.add(new Option(label, value)));
        select.value = values.some(([value]) => value === selected) ? selected : '';
    };
    const updateUnits = (selected = '') => {
        const custom = item.value === '__other__';
        other.hidden = !custom;
        otherInput.disabled = !custom;
        otherInput.required = custom;
        const units = custom ? catalog.units : (catalog.categories[category.value]?.items[item.value] || []);
        populate(unit, units.map(value => [value, value.charAt(0).toUpperCase() + value.slice(1)]), 'Select unit', selected);
        unit.disabled = !item.value;
    };
    const updateItems = (selected = '', selectedUnit = '') => {
        const items = Object.keys(catalog.categories[category.value]?.items || {}).map(value => [value, value]);
        if (category.value) items.push(['__other__', 'Other item (specify)']);
        populate(item, items, category.value ? 'Select an item' : 'Select a category first', selected);
        item.disabled = !category.value;
        updateUnits(selectedUnit);
    };
    category.addEventListener('change', () => updateItems());
    item.addEventListener('change', () => updateUnits());
    updateItems(item.dataset.selected, unit.dataset.selected);
    // Draft restoration may happen after deferred scripts; refresh dependent choices.
    window.addEventListener('pageshow', () => updateItems(item.value, unit.value));
})();
