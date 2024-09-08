import { Card, Page, Text, List } from "@shopify/polaris";
import { TitleBar } from "@shopify/app-bridge-react";
import { useTranslation } from "react-i18next";

export default function CeateTask() {
  const { t } = useTranslation();
  return (
    <Page>
      <TitleBar
        title={t("createTask.pageName")}
        primaryAction={{
          content: t("PageName.primaryAction"),
          onAction: () => console.log("Primary action"),
        }}
        secondaryActions={[
          {
            content: t("PageName.secondaryAction"),
            onAction: () => console.log("Secondary action"),
          },
        ]}
      />
        <Card>
                <Text variant="headingXl" fontWeight="bold">
                    Create Custom Task
                </Text>
                <List>
                <List.Item>Felix Crafford</List.Item>
                <List.Item>Ezequiel Manno</List.Item>
                </List>
        </Card>
    </Page>
  );
}
