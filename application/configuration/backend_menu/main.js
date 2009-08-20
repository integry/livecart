{
	"title": "Store management",
	"controller": "backend.index",
	"action": "index",
    "items":
	{
        "products":
		{
        	"title": "_menu_products",
        	"controller": "backend.category",
        	"action": "index",
        	"role": "category,product",
        	"icon": "image/silk/package.png",
        	"descr": "_menu_descr_products"
        },
        "orders":
		{
        	"title": "_menu_orders",
        	"controller": "backend.customerOrder",
        	"role": "order",
        	"icon": "image/silk/money.png",
        	"descr": "_menu_descr_orders"
        },
        "users":
		{
        	"title": "_menu_users",
        	"controller": "backend.userGroup",
        	"role": "userGroup",
        	"icon": "image/silk/group.png",
        	"descr": "_menu_descr_users"
        },
        "manage":
		{
        	"title": "_menu_manage",
        	"role": "page,news",
        	"icon": "image/silk/pencil.png",
        	"descr": "_menu_descr_manage",
        	"items":
			{
        		"rules":
				{
		        	"title": "_menu_pricing_rules",
		        	"controller": "backend.discount",
		        	"icon": "image/silk/calculator.png",
        			"descr": "_menu_descr_pricing_rules"
		        },
        		"pages":
				{
		        	"title": "_menu_pages",
		        	"controller": "backend.staticPage",
		        	"role": "page",
		        	"icon": "image/silk/page_white_text.png",
		        	"descr": "_menu_descr_pages"
		        },
        		"news":
				{
		        	"title": "_menu_news",
		        	"controller": "backend.siteNews",
		        	"role": "news",
		        	"icon": "image/silk/newspaper.png",
		        	"descr": "_menu_descr_news"
		        },
        		"manufacturers":
				{
		        	"title": "_menu_manufacturers",
		        	"controller": "backend.manufacturer",
		        	"role": "product",
		        	"icon": "image/silk/tag_blue.png",
		        	"descr": "_menu_descr_manufacturers"
		        }
		    }
        },
        "settings":
		{
        	"title": "_menu_settings",
        	"role": "settings,delivery,currency,language,delivery,taxes,update",
        	"icon": "image/silk/cog.png",
        	"descr": "_menu_descr_settings",
            "items":
			{
                "configuration":
				{
                	"title": "_menu_configuration",
                	"controller": "backend.settings",
                	"role": "settings",
                	"icon": "image/silk/wrench_orange.png",
                	"descr": "_menu_descr_configuration"
                },
                "deliveryzones":
				{
                	"title": "_menu_delivery_zones",
                	"controller": "backend.deliveryZone",
                	"action": "index",
                	"role": "delivery",
                	"icon": "image/silk/lorry.png",
                	"descr": "_menu_descr_delivery_zones"
                },
                "fields":
				{
                	"title": "_menu_custom_fields",
                	"controller": "backend.customField",
                	"icon": "image/silk/textfield.png",
                	"descr": "_menu_descr_custom_fields"
                },
                "modules":
				{
                	"title": "_menu_modules",
                	"controller": "backend.module",
                	"action": "index",
                	"role": "settings",
                	"icon": "image/silk/plugin.png",
                	"descr": "_menu_descr_modules"
                },
                "taxes":
				{
                	"title": "_menu_tax",
                	"controller": "backend.tax",
                	"action": "index",
                	"role": "taxes",
                	"icon": "image/silk/coins.png",
                	"descr": "_menu_descr_tax"
                },
                "currencies":
				{
                	"title": "_menu_currencies",
                	"controller": "backend.currency",
                	"action": "index",
                	"role": "currency",
                	"icon": "image/silk/money_euro.png",
                	"descr": "_menu_descr_currencies"
                },
                "languages":
				{
                	"title": "_menu_languages",
                	"controller": "backend.language",
                	"action": "index",
                	"role": "language",
                	"icon": "image/silk/world.png",
                	"descr": "_menu_descr_languages"
                },
                "update":
				{
                	"title": "_menu_update",
                	"controller": "backend.update",
                	"action": "index",
                	"role": "update",
                	"icon": "image/silk/drive_web.png",
                	"descr": "_menu_descr_update"
                }
            }
        },
        "customize":
		{
        	"title": "_menu_customize",
        	"role": "customize,template",
        	"icon": "image/silk/wand.png",
        	"descr": "_menu_descr_customize",
        	"items":
			{
        		"theme":
				{
        			"title": "_menu_theme",
        			"controller": "backend.theme",
                	"icon": "image/silk/color_wheel.png",
                	"descr": "_menu_descr_theme"
        		},
        		"live":
				{
        			"title": "_menu_customization_mode",
        			"controller": "backend.customize",
                	"role": "customize",
                	"icon": "image/silk/layout.png",
                	"descr": "_menu_descr_customization_mode"
        		},
        		"templates":
				{
        			"title": "_menu_edit_templates",
        			"controller": "backend.template",
                	"role": "template",
                	"icon": "image/silk/html.png",
                	"descr": "_menu_descr_edit_templates"
        		},
        		"email":
				{
        			"title": "_menu_email_templates",
        			"controller": "backend.template",
        			"action": "email",
                	"role": "template",
                	"icon": "image/silk/email_edit.png",
                	"descr": "_menu_descr_email_templates"
        		},
        		"css":
				{
        			"title": "_menu_css_edit",
        			"controller": "backend.cssEditor",
                	"role": "template",
                	"icon": "image/silk/css.png",
                	"descr": "_menu_descr_css_edit"
        		}
        	}
        },
        "reports":
		{
        	"title": "_menu_reports",
        	"controller": "backend.report",
        	"role": "userGroup",
        	"icon": "image/silk/chart_bar.png",
        	"descr": "_menu_descr_reports"
        },
        "tools":
		{
        	"title": "_menu_tools",
        	"icon": "image/silk/wrench.png",
        	"descr": "_menu_descr_tools",
        	"items":
			{
				"migrate":
				{
        			"title": "_menu_import",
        			"controller": "backend.databaseImport",
                	"role": "dbmigration",
                	"icon": "image/silk/database_refresh.png",
                	"descr": "_menu_descr_import"
        		},
        		"csv":
				{
        			"title": "_menu_import_csv",
        			"controller": "backend.csvImport",
                	"role": "csvimport",
                	"icon": "image/silk/page_white_excel.png",
                	"descr": "_menu_descr_import_csv"
        		},
        		"newsletter":
				{
        			"title": "_menu_newsletter",
        			"controller": "backend.newsletter",
                	"role": "newsletter",
                	"icon": "image/silk/email_go.png",
                	"descr": "_menu_descr_newsletter"
        		}
			}
   		}
    }
}