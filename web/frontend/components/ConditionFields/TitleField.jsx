import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function TitleField() {
  const { t } = useTranslation();
  const [value, setValue] = useState("");

  return (
    <>
        <BlockStack gap={100}>
            <Text>Where product title contains</Text>
            <InlineStack gap={200}>
                    <TextField
                        type="text"
                        placeholder="new T-shirt"
                        value={value}
                        // onChange={handleValueChange}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
