<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Models\Transaksi;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $label = 'Data Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section Data Pelanggan
                Forms\Components\Section::make('Data Pelanggan')
                    ->relationship('pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pelanggan')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('no_hp')
                            ->label('Nomor HP')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('alamat')
                            ->label('Alamat')
                            ->required()
                    ])
                    ->columns(2),

                // Section Produk yg dibeli
                Forms\Components\Section::make('Produk yang dibeli')
                    ->schema([
                        Forms\Components\Repeater::make('transaksi_details')
                            ->label('Items')
                            ->relationship('transaksi_details')
                            ->schema([
                                Forms\Components\Select::make('produk_id')
                                    ->label('Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $produk = Produk::find($state);
                                        if ($produk) {
                                            // Set harga produk
                                            $set('harga', $produk->harga);

                                            // Jika jumlah sudah ada, hitung total harga
                                            $jumlah = $get('jumlah') ?? 1;
                                            $set('total_harga', $jumlah * $produk->harga);

                                            // Update total pembayaran
                                            $transaksiDetails = $get('../../transaksi_details');
                                            $totalPembayaran = collect($transaksiDetails)->sum('total_harga');
                                            $set('../../total_pembayaran', $totalPembayaran);
                                        }
                                    }),

                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $harga = $get('harga') ?? 0;
                                        $set('total_harga', $state * $harga);

                                        // Update total pembayaran
                                        $transaksiDetails = $get('../../transaksi_details');
                                        $totalPembayaran = collect($transaksiDetails)->sum('total_harga');
                                        $set('../../total_pembayaran', $totalPembayaran);
                                    }),

                                Forms\Components\TextInput::make('harga')
                                    ->label('Harga')
                                    ->numeric()
                                    ->readonly()
                                    ->required(),

                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->readonly()
                                    ->required(),
                            ])
                            ->columns(4)
                    ]),

                // Section Pembayaran
                Forms\Components\Section::make('Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('total_pembayaran')
                            ->label('Total Pembayaran')
                            ->numeric()
                            ->readonly()
                            ->required(),

                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'gopay' => 'Gopay'
                            ])
                            ->reactive() // Enable reactivity
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'gopay') {
                                    $set('qris_image', true);
                                } else {
                                    $set('qris_image', false);
                                }
                            }),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal Transaksi'),
                Tables\Columns\TextColumn::make('total_pembayaran')
                    ->label('Total Transaksi')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode Pembayaran'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
