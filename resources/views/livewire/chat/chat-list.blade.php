<div
x-data="{type:'all',query:@entangle('query')}"

x-init="

setTimeout(() => {
conversationElement = document.getElementById('conversation-'+query);

//scroll to the element

if(conversationElement)
{
conversationElement.scrollIntoView({'behavior':'smooth'});

}
}
),200;

"
class="flex flex-col transition-all h-full overflow-hidden">

    <header class="px-3 z-10 bg-white sticky top-0 w-full py-2">

        <div class="border-b justify-between flex items-center pb-2">


            <div class="flex items-center gap-2">
                <h5 class="font-extrabold text-2xl">Chats</h5>
            </div>

            <button>
                <svg class=" w-7 h-7" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
                  </svg>
            </button>

        </div>
        {{-- Filters --}}
        <div class="flex gap-3 items-center overflow-x-scroll p-2 bg-white">
            <button @click="type='all'" :class="{'bg-blue-100 border-0 text-black':type=='all'}" class="inline-flex justify-center items-center rounded-full gap-x-1 text-xs font-medium px-3 lg:px-5 py-1 lg:py-2.5 border">
             All
            </button>

            <button @click="type='SMS'" :class="{'bg-blue-100 border-0 text-black':type=='SMS'}" class="inline-flex justify-center items-center rounded-full gap-x-1 text-xs font-medium px-3 lg:px-5 py-1 lg:py-2.5 border">
                SMS
               </button>

               <button @click="type='Whatsapp'" :class="{'bg-blue-100 border-0 text-black':type=='Whatsapp'}" class="inline-flex justify-center items-center rounded-full gap-x-1 text-xs font-medium px-3 lg:px-5 py-1 lg:py-2.5 border">
                Whatsapp
               </button>

               <button @click="type='Deleted'" :class="{'bg-blue-100 border-0 text-black':type=='Deleted'}" class="inline-flex justify-center items-center rounded-full gap-x-1 text-xs font-medium px-3 lg:px-5 py-1 lg:py-2.5 border">
                Deleted
               </button>
        </div>
    </header>

    <main class=" overflow-y-scroll overflow-hidden grow h-full relative " style="contain:content">

        {{-- chatlist --}}
        <ul class="p-2 grid w-full space-y-2">

            @if ($conversations)

            @foreach ($conversations as $conversation)


            <li
            id="conversation-{{$conversation->id}}" wire:key="{{$conversation->id}}"
            class="py-3 hover:bg-gray-50 rounded-2xl dark:hover:bg-gray-700/70 transition-colors duration-150 flex gap-4 relative w-full cursor-pointer px-2 {{$conversation->id == $selectedConversation?->id ? 'bg-gray-100/70':''}}">
            <a href="#" class="shrink-0">
                <x-avatar />
            </a>

            <aside class="grid grid-cols-12 w-full">

                <a href="{{route('chat', $conversation->id)}}" class="col-span-11 border-b pb-2 border-gray-200 relative overflow-hidden truncate leading-5 w-full flex-nowrap p-1">

                    {{-- name and date --}}
                    <div class="flex justify-between w-full items-center">
                        <h6 class="truncate font-medium tracking-wider text-gray-900">
                            {{$conversation->recipient->name}}
                        </h6>

                        <small class="text-gray-700">{{$conversation->message?->last()?->created_at?->shortAbsoluteDiffForHumans()}}</small>
                    </div>

                    {{-- Message body --}}
                    <div class="flex gap-x-2 items-center">

                        {{-- double tick --}}
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                            </svg>
                        </span>

                        <p class="grow truncate text-sm font-[100]">
                            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Suscipit, id ipsum tempore dolore ut molestias aliquam. Eius dicta nemo qui! Beatae rerum eaque quas facere aliquam, voluptatem labore! Ut, vero?
                        </p>

                        {{-- unread count --}}
                        <span class="font-bold p-px px-2 text-xs shrink-0 rounded-full bg-blue-500 text-white">5</span>
                    </div>

                </a>

                {{-- Badge for conversation type --}}
                <div class="col-span-1 flex flex-col items-center my-auto">
                    <span
                        class="inline-flex justify-center items-center rounded-full gap-x-1 text-xs font-medium px-3 py-1 lg:px-3 lg:py-2.5 border"
                        :class="{
                            'bg-green-100 text-black': {{$conversation->conversation_type}} === 'SMS',
                            'bg-yellow-100 text-black': {{$conversation->conversation_type}} === 'Whatsapp'
                        }"
                    >
                        {{ ucfirst($conversation->conversation_type) }}
                    </span>
                </div>
            </aside>
        </li>


            @endforeach

            @else

            @endif
        </ul>
    </main>
</div>
