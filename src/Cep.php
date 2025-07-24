<?php

namespace Leandrocfe\FilamentPtbrFormFields;

use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Component as Livewire;

class Cep extends TextInput
{
    public function viaCep(string $mode = 'suffix', string $errorMessage = 'CEP inválido.', string $nextFocusField = 'number', array $setFields = []): static
    {
        $viaCepRequest = function ($state, $livewire, $set, $component, $errorMessage, array $setFields, $nextFocusField) {

            $livewire->validateOnly($component->getKey());

            $request = Http::get(config('filament-ptbr-form-fields.viacep_url').$state.'/json/')->json();

            foreach ($setFields as $key => $value) {
                $set($key, $request[$value] ?? null);
            }

            if (blank($request) || Arr::has($request, 'erro')) {
                throw ValidationException::withMessages([
                    $component->getKey() => $errorMessage,
                ]);
            }

            $nextFocusTargetComponent = collect($component->getContainer()->getComponents())
                ->filter(fn ($formComponent): bool => str_contains($formComponent->cachedAbsoluteKey, $nextFocusField))
                ->first();

            $livewire->js("document.getElementById('{$nextFocusTargetComponent->cachedAbsoluteKey}').focus()");
        };

        $this
            ->minLength(9)
            ->mask('99999-999')
            ->afterStateUpdated(function ($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $viaCepRequest, $nextFocusField) {
                $viaCepRequest($state, $livewire, $set, $component, $errorMessage, $setFields, $nextFocusField);
            })
            ->suffixAction(function () use ($mode, $errorMessage, $setFields, $viaCepRequest, $nextFocusField) {
                if ($mode === 'suffix') {
                    return Action::make('search-action')
                        ->label('Buscar CEP')
                        ->icon('heroicon-o-magnifying-glass')
                        ->action(function ($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $viaCepRequest, $nextFocusField) {
                            $viaCepRequest($state, $livewire, $set, $component, $errorMessage, $setFields, $nextFocusField);
                        })
                        ->cancelParentActions();
                }
            })
            ->prefixAction(function () use ($mode, $errorMessage, $setFields, $viaCepRequest, $nextFocusField) {
                if ($mode === 'prefix') {
                    return Action::make('search-action')
                        ->label('Buscar CEP')
                        ->icon('heroicon-o-magnifying-glass')
                        ->action(function ($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $viaCepRequest, $nextFocusField) {
                            $viaCepRequest($state, $livewire, $set, $component, $errorMessage, $setFields, $nextFocusField);
                        })
                        ->cancelParentActions();
                }
            });

        return $this;
    }
}
