const mix = require('laravel-mix')

mix.webpackConfig({
  resolve: {
    extensions: ['.js', '.vue'],
    alias     : {
      '@': __dirname + '/resources'
    }
  }
})

mix
    .js('resources/js/moderator/moderator.js', 'public/js').vue()
    .sass('resources/sass/moderator.scss', 'public/css')
//.js('resources/js/operator.js', 'public/js').vue()
//.js('resources/js/collectorContainer.js', 'public/js/collectorContainer.js').vue()
//.sass('resources/sass/collectorContainer.scss', 'public/css')
//.js('resources/js/PartnerPackageStatus.js', 'public/js/PartnerPackageStatus.js').vue()
//.sass('resources/sass/PartnerPackageStatus.scss', 'public/css')
//.js('resources/js/CanadaShopPackageStatus.js', 'public/js/CanadaShopPackageStatus.js').vue()
//.sass('resources/sass/CanadaShopPackageStatus.scss', 'public/css')
 //   .js('resources/js/courier.js', 'public/js/courier.js').vue()
//    .sass('resources/sass/courier.scss', 'public/css')
//    .js('resources/js/operatorCourier.js', 'public/js/operatorCourier.js').vue()
//    .sass('resources/sass/operatorCourier.scss', 'public/css')
/*
.js('resources/js/cashier.js', 'public/js')
.js('resources/js/courier.js', 'public/js')
.js('resources/js/operatorCourier.js', 'public/js')
.js('resources/js/courier_user.js', 'public/js')
*/
// .js('resources/js/moderator/moderator.js', 'public/js/moderator.js')
  /*.js('resources/js/operator.js', 'public/js')
  .js('resources/js/operatorCourier.js', 'public/js')
  .js('resources/js/cashier.js', 'public/js')
  .js('resources/js/anonymous.js', 'public/js')
  .js('resources/js/cashierCourier.js', 'public/js')
  .js('resources/js/makeOrder.js', 'public/js')*/
  /*
  .js('resources/js/packages.js', 'public/js')

  .js('resources/js/new_courier_order.js', 'public/js')*/
  // .ts('resources/js/sub_accounts.ts', 'public/js/sub_accounts.js')
 /* .js('resources/js/customs.js', 'public/js')*/
  // .js('resources/js/delivery.js', 'public/js')
  // .js('resources/js/distributor.js', 'public/js')
  /* .js('resources/js/report.js', 'public/js')
   .js('resources/js/courier.js', 'public/js')
   .js('resources/js/courier_user.js', 'public/js')
  .sass('resources/sass/makeOrder.scss', 'public/css')
  .sass('resources/sass/cashier.scss', 'public/css')*/
  //.sass('resources/sass/operator.scss', 'public/css')
  /*.sass('resources/sass/operator.scss', 'public/css')
   .sass('resources/sass/packages.scss', 'public/css')
    .sass('resources/sass/anonymous.scss', 'public/css')
    .sass('resources/sass/sub_accounts.scss', 'public/css')
    .sass('resources/sass/operatorCourier.scss', 'public/css')*/
  /*.sass('resources/sass/new_courier_order.scss', 'public/css')
  .sass('resources/sass/delivery.scss', 'public/css')
  .sass('resources/sass/report.scss', 'public/css')
  .sass('resources/sass/distributor.scss', 'public/css')
  .sass('resources/sass/customs.scss', 'public/css')*/
  /*.sass('resources/sass/courier.scss', 'public/css')
  .sass('resources/sass/courier_user.scss', 'public/css')
  .sass('resources/sass/cashierCourier.scss', 'public/css')*/
  .browserSync('localhost:8000')

/*mix
  .js('resources/js/moderator/moderator.js', 'public/js/moderator.js')
  .sass('resources/sass/moderator.scss', 'public/css')
  .browserSync('localhost:8000')*/
