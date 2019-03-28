# Dienstleistungsassistent
 
Der Dienstleistungsassistent erleichtert das Anbieten von Dienstleistungen auf plentyMarketplace, indem er es ermöglicht, das in plentymarkets vorhandene Ticketsystem anstelle von externen Webhooks zu verwenden.

## Erste Schritte und Voraussetzungen

Dieses Plugin kann nur verwendet werden, wenn Sie eine Dienstleistung auf plentyMarketplace anbieten. Weitere Informationen zum Anbieten von Dienstleistungen finden Sie unter **Verwandte Themen**. Außerdem muss im Ticketsystem mindestens eine Rolle, ein Typ und ein Status definiert sein. Weitere Informationen dazu finden Sie [hier](https://knowledge.plentymarkets.com/crm/ticketsystem-nutzen#700).

Nachdem Sie das Plugin erworben haben, muss es installiert und aktiviert werden. Weitere Informationen zum Installieren und Aktivieren von Plugins finden Sie [hier](https://knowledge.plentymarkets.com/plugins/plugin-sets#plugins-hinzufuegen).
 
## Plugin-Konfiguration

Nach der Installation muss das Plugin noch eingerichtet werden. Klicken Sie dazu auf den Namen des Plugin in der **Plugin-Übersicht**. Ändern Sie nicht die angegebene Route in der Registerkarte **Webhook-URL**. Wird diese hier geändert, muss auch der entsprechende Teil der **webhookURL** in der plugin.json-Datei des Service-Plugins angepasst werden.

Öffnen Sie die Registerkarte **Ticket**. Geben Sie dort die IDs der zuvor erstellten Rolle, des Typs und des Statuses ein. Geben Sie außerdem die **IDs** der Benutzer ein, die Zugriff auf die Tickets haben sollen, die angelegt werden, wenn ein Kunde Ihre Dienstleistung erwirbt. Die ID ist im Menü **System » Einstellungen » Benutzer » Konten** hinterlegt, wenn Sie auf den Namen des Benutzers klicken.
 
**Speichern** Sie die Einstellungen.

## Automatisierung

Nachdem Sie das Plugin eingerichtet haben, können Sie Ihre Arbeitsabläufe noch automatisieren. Nutzen Sie die Möglichkeit, [automatisch E-Mails zu versenden](https://knowledge.plentymarkets.com/crm/ticketsystem-nutzen#2900), um auf eingehende Tickets direkt zu reagieren und Ihre Kunden über die nächsten Schritte zu informieren.

## Verwandte Themen

Weitere Informationen dazu, wie Sie Dienstleistungen auf plentyMarketplace anbieten können, finden Sie auf folgenden Seiten (nur auf Englisch):
 
* [Services requirements](https://developers.plentymarkets.com/marketplace/services-requirements)
* [Service plugin tutorial](https://developers.plentymarkets.com/tutorials/service-plugin)
 
## Lizenz
 
Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/ksted/MarketplaceService/blob/master/LICENSE.md)-Datei.