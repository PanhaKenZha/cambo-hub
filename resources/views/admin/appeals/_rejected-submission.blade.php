{{--
    Lets the person who submitted content appeal a rejection from the editor page.
    Needs: $review (the rejected ApprovalRequest).
--}}
@if($review->submitted_by === auth()->id())
    @if($review->appeals()->where('status', \App\Enums\AppealStatus::Pending)->exists())
        <p class="mt-2 text-sm font-medium">Your appeal is being reviewed by another moderator.</p>
    @else
        <details class="mt-2" @if($errors->getBag('appeal')->any()) open @endif>
            <summary class="cursor-pointer text-sm font-semibold underline">Disagree? Appeal this decision</summary>
            <form method="POST" action="{{ route('appeals.store') }}" class="mt-3 space-y-3" data-loading>
                @csrf
                <input type="hidden" name="subject" value="submission">
                <input type="hidden" name="id" value="{{ $review->id }}">
                <label for="appeal-message" class="sr-only">Why is this decision wrong?</label>
                <textarea id="appeal-message" name="message" rows="3" required minlength="20" maxlength="2000" class="form-control rounded-lg text-ink"
                          placeholder="Explain why the content should be published. A different moderator reviews appeals.">{{ old('message') }}</textarea>
                @error('message', 'appeal')<p class="text-sm" role="alert">{{ $message }}</p>@enderror
                @error('appeal', 'appeal')<p class="text-sm" role="alert">{{ $message }}</p>@enderror
                <x-admin.button variant="secondary" loading-text="Sending…">Send appeal</x-admin.button>
            </form>
        </details>
    @endif
@endif
