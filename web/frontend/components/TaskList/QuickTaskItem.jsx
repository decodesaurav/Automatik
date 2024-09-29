import { BlockStack, Card,Text, InlineStack, RadioButton, Tag } from "@shopify/polaris";
import { useTranslation } from "react-i18next";

export default function QuickTaskItem({taskItem}) {
  const { t } = useTranslation();

  return (
    <>
    <BlockStack gap={200}>
        <Card>
            <BlockStack gap={100}>
                <Text variant="headingSm">{taskItem.title}</Text>
                <Text>{taskItem.description}</Text>
                <InlineStack gap={200}>
                    {taskItem.tags.map((tag, index) => (
                        <Tag key={index}>{tag}</Tag>
                    ))}
                </InlineStack>
            </BlockStack>
        </Card>
    </BlockStack>
    </>
  );
}