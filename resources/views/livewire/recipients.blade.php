<div class="max-w-6xxl mx-auto my-16">

    <h6 class="text-center text-5xl font-bold py-3">Contacts</h6>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 p-2">

        @foreach ($recipients as $recipient )



        {{--child --}}
        <div class="w-full bg-white border-gray-200 rounded-lg p-5 shadow">

            <div class="flex flex-col items-center pb-10">


                <img src="https://source.unsplash.com/500x500?face" alt="" class="w-24 h-24 mb-2 rounded-full shadow-lg">

                <h5 class="mb-1 text-xl font-medium text-gray-900">
                    {{ $recipient->name }}
                </h5>

                <span class="text-sm text-gray-500">{{ $recipient->phone_number }}</span>

                <div class="flex mt-4 space-x-3 md:mt-6">

                    <x-primary-button wire:click="sms({{$recipient->id}})">
                        SMS
                    </x-primary-button>

                    <x-primary-button wire:click="whatsapp({{$recipient->id}})">
                        Whatsapp
                    </x-primary-button>


                </div>

            </div>


        </div>

        @endforeach
    </div>

</div>
