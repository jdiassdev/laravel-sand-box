<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Resources\ProdutoResource\RelationManagers;
use App\Models\Produto;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput gera um <input type="text"> com label automatico
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),

                // numeric() muda o teclado no mobile e valida que e numero
                TextInput::make('preco')
                    ->required()
                    ->numeric()
                    ->prefix('R$'),

                // Textarea gera um <textarea> — nullable no banco, entao nao e required aqui
                Textarea::make('descricao')
                    ->columnSpanFull(), // ocupa a linha inteira do grid
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn exibe o valor de uma coluna do banco
                TextColumn::make('nome')
                    ->searchable(), // adiciona campo de busca por esse coluna

                TextColumn::make('preco')
                    ->money('BRL'), // formata como moeda brasileira

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->label('Criado em'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),
        ];
    }
}
