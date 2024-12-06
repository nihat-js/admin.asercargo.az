import Vue    from 'vue'
import Router from 'vue-router'

let ProhibitedItems  = () => import('./views/ProhibitedItems.vue')
let Store            = () => import('./views/Store.vue')
let StoreCategory    = () => import('./views/StoreCategory.vue')
let Category         = () => import('./views/Category.vue')
let FAQ              = () => import('./views/FAQ.vue')
let CountryDetails   = () => import('./views/CountryDetails.vue')
let MailSMSTemplate  = () => import('./views/MailSMSTemplate.vue')
let TemplateLanguage = () => import('./views/TemplateLanguage.vue')
let Status = () => import('./views/Status.vue')

Vue.use(Router)

export default new Router({
  mode  : 'history',
  base  : process.env.BASE_URL,
  routes: [
    {
      path     : '/moderator/FAQ',
      name     : 'FAQ',
      component: FAQ,
    },
    {
      path     : '/moderator/prohibited-items',
      name     : 'ProhibitedItems',
      component: ProhibitedItems,
    },
    {
      path     : '/moderator/store',
      name     : 'Store',
      component: Store,
    },
    {
      path     : '/moderator/store_category',
      name     : 'StoreCategory',
      component: StoreCategory,
    },
    {
      path     : '/moderator/category',
      name     : 'Category',
      component: Category,
    },
    {
      path     : '/moderator/country_details',
      name     : 'CountryDetails',
      component: CountryDetails,
    },
    {
      path     : '/moderator/mail_sms_template',
      name     : 'MailSMSTemplate',
      component: MailSMSTemplate,
    },
    {
      path     : '/moderator/language/:group',
      name     : 'TemplateLanguage',
      component: TemplateLanguage,
    },
    {
      path     : '/moderator/status',
      name     : 'Status',
      component: Status,
    }
  ],
})
