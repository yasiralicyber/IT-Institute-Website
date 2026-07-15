<?php $wa = config('institute.whatsapp'); ?>
<a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode('Hello! I am interested in your IT courses.') ?>"
   target="_blank" rel="noopener"
   class="group fixed bottom-5 right-5 z-50 flex items-center gap-3 rounded-full bg-[#25D366] py-3 pl-3 pr-4 text-white shadow-2xl shadow-green-500/40 transition hover:scale-105"
   aria-label="Chat on WhatsApp">
    <span class="absolute inline-flex h-12 w-12 animate-ping rounded-full bg-[#25D366] opacity-40"></span>
    <svg class="h-7 w-7" viewBox="0 0 32 32" fill="currentColor"><path d="M16 .5C7.4.5.5 7.4.5 16c0 2.8.7 5.4 2 7.7L.5 31.5l8-2.1c2.2 1.2 4.8 1.9 7.5 1.9 8.6 0 15.5-6.9 15.5-15.5S24.6.5 16 .5zm0 28.2c-2.4 0-4.7-.6-6.7-1.8l-.5-.3-4.7 1.2 1.3-4.6-.3-.5a12.7 12.7 0 01-1.9-6.7C2.5 8.6 8.6 2.5 16 2.5S29.5 8.6 29.5 16 23.4 28.7 16 28.7zm7.3-9.5c-.4-.2-2.4-1.2-2.7-1.3-.4-.1-.6-.2-.9.2-.3.4-1 1.3-1.3 1.6-.2.3-.5.3-.9.1-.4-.2-1.7-.6-3.2-2-1.2-1-2-2.4-2.2-2.8-.2-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.2.3-.4.4-.7.1-.3 0-.5 0-.7-.1-.2-.9-2.2-1.3-3-.3-.8-.6-.7-.9-.7h-.7c-.3 0-.7.1-1 .5-.4.4-1.4 1.3-1.4 3.2s1.4 3.7 1.6 4c.2.3 2.8 4.3 6.8 6 .9.4 1.7.6 2.3.8.9.3 1.8.3 2.5.2.8-.1 2.4-1 2.7-1.9.3-.9.3-1.7.2-1.9-.1-.2-.4-.3-.8-.5z"/></svg>
    <span class="max-w-0 overflow-hidden whitespace-nowrap text-sm font-bold opacity-0 transition-all duration-300 group-hover:max-w-[160px] group-hover:opacity-100">Chat with us</span>
</a>
