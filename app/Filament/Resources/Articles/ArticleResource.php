<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Filament\Resources\Articles\Tables\ArticlesTable;
use App\Models\Article;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }


// Peut-il voir la liste ?
    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('view articles') : false;
    }

    // Peut-il créer ?
    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('create articles') : false;
    }

    // Peut-il modifier ce record ?
    // Model $record (pas Article) → compatible avec la classe parente Filament
    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user 
    ? (
        $user->hasRole('admin') ||
        ($user->can('update articles') && $record->statut !== 'Réformé')
      ) 
    : false;
    }

    // Peut-il supprimer ce record ?
    public static function canDelete(Model $record): bool
    {/** @var User|null $user */
        
        $user = Auth::user();
        return $user ? ($user->can('delete articles') && $record->statut !== 'Réformé') : false;
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
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
   
}
