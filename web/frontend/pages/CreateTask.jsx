import { Card, Page, Text, List } from "@shopify/polaris";
import { TitleBar } from "@shopify/app-bridge-react";
import { useTranslation } from "react-i18next";
import Task from "../components/CustomTask/Task";

export default function CreateTask() {
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
      <Task />
    </Page>
  );
}
