---
Bundle created to support communication with Salesmanago - based on Pixers sending package.

By default, configuration is loaded from either DemoBundle, or needs to be injected via Settings API

Sample request to set proper credentials:
/api/settings
Method: POST
type: application/json

{
	"settings":
	{
	      // ... rest of the settings values
	      "marketingVendorsValue": "sales_manago"
	      "sales_manago":
	      {
                "api_url": "http://www.salesmanago.pl/api",
                "api_secret": "secret",
                "api_key": "key",
                "customer_id": "custid",
                "email": "mail@mail.com."
	      }
	      // ... rest of the settings values
	}
	
}

Important notes:
Plugin works as a bald sender, with no great way to support sending failed requests again. 
All information about what happened to requests are stored in plugin.log, in var/logs. 

If request fails, it goes to deadletter table in database, as serialised object with repeat counter - later it will may be used in command, to send it one more time, 
for example if API falls. 

Right now there is no such command, yet it is simple. 


Due to Salesmanago and project specification, there is a need of translations - located in messages.pl.yml.
