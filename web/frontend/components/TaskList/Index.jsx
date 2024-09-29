import { BlockStack, Card,Text, InlineStack, RadioButton, Tag, Badge, ButtonGroup, Button } from "@shopify/polaris";
import { useTranslation } from "react-i18next";

export default function TaskItem({taskItem}) {
  const { t } = useTranslation();

  return (
    <>
    <BlockStack gap={200}>
        <Card>
            <InlineStack>
                <BlockStack gap={100}>
                <Text variant="headingSm">{taskItem.task_name}</Text>
                <Text>Scheduled Time: {(new Date(taskItem.schedule_time * 1000)).toUTCString()}</Text>
                <InlineStack gap={200}>
                    <Badge tone="success">{taskItem.task_type}</Badge>
                </InlineStack>
                </BlockStack>
                <InlineStack blockAlign="end" alignment="end"> {/* Align the buttons to the right */}
                {/* <ButtonGroup>
                    <Button>Cancel</Button>
                    <Button variant="primary">Save</Button>
                </ButtonGroup> */}
                </InlineStack>
            </InlineStack>
        </Card>
    </BlockStack>
    </>
  );
}