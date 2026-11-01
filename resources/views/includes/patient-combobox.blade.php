{{--
    Type-ahead picker markup: one field that searches and lists at the same time.

    Pair this with the page's existing <select class="rc-combo-store"> inside the
    same [data-combo] wrapper — public/js/rc-combobox.js builds the list from that
    select's options and keeps it as the submitted value, so name, old() and the
    server-side validation of the real field all stay exactly as they were.

    Expects:
      $cbInput       (string) unique id for the text input
      $cbPlaceholder (string)
      $cbRequired    (bool)   set when the field is mandatory
      $cbInvalid     (bool)   set when the paired field has a validation error
--}}
@php
    $cbInput = $cbInput ?? 'rcCombo';
    $cbList = $cbInput.'-list';
    $cbPlaceholder = $cbPlaceholder ?? 'Type to search...';
@endphp
<div class="rc-combo-field">
    <input type="text" class="form-control rc-combo-input{{ ! empty($cbInvalid) ? ' is-invalid' : '' }}" id="{{ $cbInput }}"
           placeholder="{{ $cbPlaceholder }}" autocomplete="off" autocorrect="off" spellcheck="false"
           role="combobox" aria-expanded="false" aria-controls="{{ $cbList }}" aria-autocomplete="list"
           @if (! empty($cbRequired)) required @endif>
    <button type="button" class="rc-combo-toggle" tabindex="-1" aria-label="Show the full list" aria-controls="{{ $cbList }}">
        <i class="bi bi-chevron-down"></i>
    </button>
    <div class="rc-combo-list" id="{{ $cbList }}" role="listbox" hidden></div>
</div>
