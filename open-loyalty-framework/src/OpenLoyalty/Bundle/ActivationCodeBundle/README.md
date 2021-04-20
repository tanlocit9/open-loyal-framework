# Sms gateways

There are three available gateways:
- world text
- sms api
- dummy

## Setting proper gateway

In config_prod.yml (or config_dev.yml for development purpose) set choosen gateway
```
open_loyalty_activation_code:
  sms_gateway: world_text # available gateways: world_text, sms_api, dummy
```

### Dummy gateway

This gateway is for testing purposes. It logs message in file var/logs/<env>_sms.log


