<div class="faq-list">
    @foreach ($faqs as $faq)
    <details class="faq-item">
        <summary>{{ $faq['question'] }}</summary>
        <p>{{ $faq['answer'] }}</p>
    </details>
    @endforeach
</div>
