@if(session('status'))
    <x-ui.alert variant="success" class="mb-6">{{ __(session('status')) }}</x-ui.alert>
@endif
